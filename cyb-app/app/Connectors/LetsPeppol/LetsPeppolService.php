<?php

namespace App\Connectors\LetsPeppol;

use App\Connectors\LetsPeppol\ACube\ACube;
use App\Connectors\LetsPeppol\Models\Identity;
use App\Connectors\LetsPeppol\Models\Message;
use App\Core\ApplicationManager;
use App\Core\Helper;
use App\Core\Settings;
use App\Models\Authentication;

Helper::include_once(__DIR__.'/PonderSource');

use OCA\PeppolNext\PonderSource\UBL\Invoice\Invoice;

class LetsPeppolService
{
    public const REGISTRAR_AS4_DIRECT = 'as4-direct';
    public const REGISTRAR_ACUBE = 'acube';

    private const SETTINGS_FILE = __DIR__.'/settings.json';

    private Settings $settings;
    private ACube $acube;

    public function __construct()
    {
        $this->settings = new Settings(self::SETTINGS_FILE);
        $this->acube = new ACube();

        $has_changes = false;

        if (empty($this->settings['acube-incoming'])) {
            try {
                $webhooks = $this->acube->getWebhooks();

                if (!empty($webhooks)) {
                    foreach ($webhooks as $webhook) {
                        if ($webhook['event'] === 'incoming-document') {
                            $this->settings['acube-incoming'] = $webhook['uuid'];
                            $has_changes = true;
                        }
                        else if ($webhook['event'] === 'outgoing-document') {
                            $this->settings['acube-outgoing'] = $webhook['uuid'];
                            $has_changes = true;
                        }
                    }
                }

                if (empty($this->settings['acube-incoming'])) {
                    $uuid = $this->acube->createWebhook(route('connector.lets_peppol.acube-incoming'), true);
                    
                    if (!empty($uuid)) {
                        $this->settings['acube-incoming'] = $uuid;
                        $has_changes = true;
                    }
                }
            } catch (\Exception $e) {
                
            }
        }

        if (empty($this->settings['acube-outgoing'])) {
            try {
                $uuid = $this->acube->createWebhook(route('connector.lets_peppol.acube-outgoing'), false);
                
                if (!empty($uuid)) {
                    $this->settings['acube-outgoing'] = $uuid;
                    $has_changes = true;
                }
            } catch (\Exception $e) {
                
            }
        }

        if ($has_changes) {
            $this->settings->save();
        }
    }

    /**
     * Properties are:
     * name, address, city, region, country, zip
     */
    public function createIdentity(array $properties): ?Identity
    {
        $identity = new Identity();
        $identity['name'] = $properties['name'];
        $identity['address'] = $properties['address'];
        $identity['city'] = $properties['city'];
        $identity['region'] = $properties['region'];
        $identity['country'] = $properties['country'];
        $identity['zip'] = $properties['zip'];
        $identity['as4direct_endpoint'] = $properties['as4direct_endpoint'];
        $identity['as4direct_public_key'] = $properties['as4direct_public_key'];
        $identity['kyc_status'] = Identity::KYC_STATUS_PENDING_APPROVAL;

        return $identity->save() ? $identity : null;
    }

    public function getIdentities(): array
    {
        $results = Identity::orderBy('created_at', 'desc')->get();

        $identities = [];

        foreach ($results as $result) {
            $identities[] = $result->getModel();
        }

        return $identities;
    }

    public function getIdentity(int $id): ?Identity
    {
        try {
            return Identity::query()->where('id', $id)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    public function updateIdentity(Identity $identity): bool
    {
        Identity::beginTransaction();

        if (!$identity->save()) {
            Identity::rollBack();
            return false;
        }

        $should_be_registered = $identity['kyc_status'] == Identity::KYC_STATUS_APPROVED;
        $is_registered = !empty($identity['registrar']) && !empty($identity['reference']);

        if ($should_be_registered) {
            if (!$is_registered) {
                if (empty($identity['identifier_scheme']) || empty($identity['identifier_value'])) {
                    Identity::rollBack();
                    return false;
                }

                $info = [
                    'registeredName' => $identity['name'],
                    'country' => $identity['country'],
                    'address' => $identity['address'],
                    'city' => $identity['city'],
                    'stateOrProvince' => $identity['region'],
                    'zipCode' => $identity['zip'],
                    'identifierScheme' => $identity['identifier_scheme'],
                    'identifierValue' => $identity['identifier_value']
                ];

                try {
                    $reference = $this->acube->createLegalEntity($info)['uuid'];
                    
                    if ($this->acube->setInvoiceCapability($reference)) {
                        $identity['registrar'] = self::REGISTRAR_ACUBE;
                        $identity['reference'] = $reference;

                        $key_store = new KeyStore();
                        $identity['as4direct_certificate'] = $key_store->issueCertificate($identity['name'], $identity['as4direct_public_key']);
    
                        $identity->save();
                    }
                    else {
                        Identity::rollBack();
                        return false;
                    }
                } catch (\Exception $e) {
                    echo var_export($e).'<br>';
                    Identity::rollBack();
                    return false;
                }
            }
        }
        else if ($is_registered) {
            if (empty($identity['registrar']) || empty($identity['reference'])) {
                Identity::rollBack();
                return false;
            }

            if ($identity['registrar'] != self::REGISTRAR_ACUBE) {
                Identity::rollBack();
                return false;
            }

            try {
                if ($this->acube->removeLegalEntity($identity['reference'])) {
                    $identity['registrar'] = '';
                    $identity['reference'] = '';
                    $identity['as4direct_certificate'] = '';
                    
                    $identity->save();
                }
                else {
                    Identity::rollBack();
                    return false;
                }
            } catch (\Exception $e) {
                Identity::rollBack();
                return false;
            }
        }

        Identity::commit();

        return true;
    }

    public function sendMessage(int $identity_id, string $ubl): bool
    {
        $identity = $this->getIdentity($identity_id);

        if (empty($identity)) {
            // Identity not found
            return false;
        }

        if ($identity['registrar'] != self::REGISTRAR_ACUBE) {
            // No registrar
            return false;
        }

        if (empty($identity['identifier_scheme']) || empty($identity['identifier_value'])) {
            // No identifier
            return false;
        }

        $supplier_scheme = null;
        $supplier_value = null;
        
        try {
            $invoice = $this->invoiceFromUBL($ubl);
            $supplier_ID = $invoice->getAccountingSupplierParty()->getParty()->getEndpointID();

            $supplier_scheme = $supplier_ID->getSchemeID();
            $supplier_value = $supplier_ID->getValue();
        } catch (\Exception $e) {
            return false;
        }

        if ($identity['identifier_value'] != $supplier_scheme.':'.$supplier_value) {
            // Can not send invoice on behalf of someone else!
            return false;
        }

        try {
            return $this->acube->sendInvoice($ubl) != null;
        } catch (\Exception $e) {
            throw $e;
            // Connection error, internal error (token), bad UBL, double sending?no, internal error (state is lost)
            return false;
        }
    }

    public function addIncomingMessage(int $identity_id, string $ubl): bool
    {
        $identity = $this->getIdentity($identity_id);

        if (empty($identity)) {
            return false;
        }

        if ($identity['registrar'] != self::REGISTRAR_ACUBE) {
            return false;
        }

        if ($identity['registrar'] != self::REGISTRAR_ACUBE) {
            // No registrar
            return false;
        }

        if (empty($identity['identifier_scheme']) || empty($identity['identifier_value'])) {
            // No identifier
            return false;
        }

        $customer_scheme = null;
        $customer_value = null;
        
        try {
            $invoice = $this->invoiceFromUBL($ubl);
            $customer_ID = $invoice->getAccountingCustomerParty()->getParty()->getEndpointID();

            $customer_scheme = $customer_ID->getSchemeID();
            $customer_value = $customer_ID->getValue();
        } catch (\Exception $e) {
            // Bad UBL
            return false;
        }

        if ($identity['identifier_scheme'] != $customer_scheme || $identity['identifier_value'] != $customer_value) {
            // Can not add an invoice in behalf of someone else's account!
            return false;
        }

        try {
            return $this->acube->addIncomingInvoice($ubl) != null;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getMessages(int $identity_id): array
    {
        $results = Message::query()
                ->where('identity_id', $identity_id)
                ->orderBy('receive_time', 'asc')
                ->get();

        $messages = [];

        foreach ($results as $result) {
            $messages[] = $result->getModel();
        }

        return $messages;
    }

    public function getMessageContent(Message $message): ?string
    {
        if (!empty($message['file_name'])) {
            return file_get_contents(Message::STORAGE_BASE_PATH.$message['file_name']);
        }

        if ($message['registrar'] != self::REGISTRAR_ACUBE) {
            return null;
        }

        $ubl = null;

        try {
            $ubl = $self->acube->getInvoice($message['reference']);
        } catch (\Exception $e) {

        }
        
        if ($ubl == null) {
            return null;
        }

        $file_name = uniqid('message-');

        file_put_contents(Message::STORAGE_BASE_PATH.$file_name, $ubl);

        $message['file_name'] = $file_name;
        $message->save();

        return $ubl;
    }

    public function removeMessage(Message $message): bool
    {
        if (!empty($message['file_name'])) {
            unlink(Message::STORAGE_BASE_PATH.$message['file_name']);
        }

        return $message->delete();
    }

    public function removeMessages(int $identity_id, int $until): int
    {
        $removed_count = 0;

        $results = Message::query()
                ->where('identity_id', $identity_id)
                ->where('receive_time', '<=', $until)
                ->get();

        foreach ($results as $result) {
            $removed_count += $this->removeMessage($result->getModel()) ? 1 : 0;
        }

        return $removed_count;
    }

    public function newMessage(string $registrar, bool $incoming, $body)
    {
        if ($registrar != self::REGISTRAR_ACUBE) {
            return;
        }

        list($id, $type) = $this->acube->onDataReceivedFromWebHook($body);

        if (empty($id) || empty($type)) {
            return;
        }

        $message = new Message();
        $message['registrar'] = $registrar;
        $message['reference'] = $id;
        $message['type'] = $type; // Message type names are identical to acube's.
        $message['direction'] = $incoming ? Message::DIRECTION_INCOMING : Message::DIRECTION_OUTGOING;

        // This line will throw an exception if fails. Which is actually what we want to happen.
        $ubl = $this->acube->getInvoice($id);

        if ($ubl == null) {
            throw new \Exception('Failed to retrieve message content.');
        }

        $file_name = uniqid('message-');
        file_put_contents(Message::STORAGE_BASE_PATH.$file_name, $ubl);
        $message['file_name'] = $file_name;

        // read the invoice
        $invoice = $this->invoiceFromUBL($ubl);

        // discover identifier and identity
        $endpoint_ID = null;

        if ($incoming) {
            $endpoint_ID = $invoice->getAccountingCustomerParty()->getParty()->getEndpointID();
        }
        else {
            $endpoint_ID = $invoice->getAccountingSupplierParty()->getParty()->getEndpointID();
        }

        $identifier_scheme = $endpoint_ID->getSchemeID();
        $identifier_value = $endpoint_ID->getValue();

        $identity = null;

        try {
            $identity = Identity::query()
                    ->where('identifier_value', $identifier_value)
                    ->where('registrar', self::REGISTRAR_ACUBE)
                    ->where('identifier_scheme', $identifier_scheme)
                    ->first();
        } catch (\Exception $e) {
            // Identity not found. Probably at some point we had this user but didn't remove it from acube.
            // We just return because we don't want acube to send it again.
            // TODO remove the identity from acube maybe?
            return;
        }

        // complete the message object and save
        $message['identity_id'] = $identity['id'];
        $message['receive_time'] = time();
        $message->save();
        
        // check for update notifier to wake up
        if (!empty($identity['auth_id'])) {
            try {
                $auth = Authentication::query()
                    ->where('id', $identity['auth_id'])
                    ->first();

                ApplicationManager::onNewUpdate($auth, 'invoice');
            } catch (\Exception $e) {
                // If this fails, it is on us and acube sending again doesn't help it.
            }
        }
    }

    private function invoiceFromUBL(string $ubl): Invoice
    {
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
        return $serializer->deserialize($ubl, 'OCA\PeppolNext\PonderSource\UBL\Invoice\Invoice::class', 'xml');
    }

}