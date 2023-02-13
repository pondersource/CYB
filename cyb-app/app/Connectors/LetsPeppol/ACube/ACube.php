<?php

namespace App\Connectors\LetsPeppol\ACube;

use Illuminate\Support\Facades\Http;

/**
 * All functions may throw Illuminate\Http\Client\ConnectionException
 */
class ACube
{
    private Authentication $authentication;

    public function __construct()
    {
        $this->authentication = new Authentication();
    }

    public function getWebhooks(): ?array
    {
        $response = $this->prepareRequest()->get(Constants::BASE_URL.'/webhooks');

        if ($response->successful()) {
            return $response->json();
        }
        else {
            return null;
        }
    }

    public function createWebhook(string $url, bool $incoming): ?string
    {
        $body = [
            'event' => $incoming ? 'incoming-document' : 'outgoing-document',
            'url' => $url
        ];

        $response = $this->prepareRequest()->post(Constants::BASE_URL.'/webhooks', $body);

        if ($response->successful()) {
            return $response['uuid'];
        }
        else {
            return null;
        }
    }

    public function removeWebhook(string $uuid): bool
    {
        $response = $this->prepareRequest()->delete(Constants::BASE_URL.'/webhooks/'.$uuid);

        if ($response->successful()) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Info are:
     * - registeredName
     * - country
     * - address
     * - city
     * - stateOrProvince
     * - zipCode
     * - identifierScheme
     * - identifierValue
     * Response is the same object added:
     * - uuid
     * - smpEnabled
     * - createdAt
     * - updatedAt
     * 
     * More info: https://docs.acubeapi.com/documentation/peppol/peppol/tag/LegalEntity/#tag/LegalEntity/operation/postLegalEntityCollection
     */
    public function createLegalEntity(array $info): ?array
    {
        $info['receivedDocumentNotificationEmails'] = [];

        $response = $this->prepareRequest()
            ->post(Constants::BASE_URL.'/legal-entities', $info);

        if ($response->successful()) {
            return $response->json();
        }
        else {
            return null;
        }
    }

    public function getLegalEntity(string $uuid): ?array
    {
        $response = $this->prepareRequest()
            ->get(Constants::BASE_URL.'/legal-entities/'.$uuid);

        if ($response->successful()) {
            return $response->json();
        }
        else {
            return null;
        }
    }

    public function updateLegalEntity(string $uuid, array $info): ?array
    {
        $info['receivedDocumentNotificationEmails'] = [];

        $response = $this->prepareRequest()
            ->put(Constants::BASE_URL.'/legal-entities/'.$uuid, $info);

        if ($response->successful()) {
            return $response->json();
        }
        else {
            return null;
        }
    }

    public function removeLegalEntity(string $uuid): bool
    {
        $response = $this->prepareRequest()
            ->delete(Constants::BASE_URL.'/legal-entities/'.$uuid);

        if ($response->successful()) {
            return true;
        }
        else {
            return false;
        }
    }

    public function setInvoiceCapability(string $uuid): bool
    {
        return $this->setLegalEntityCapabilities($uuid, [
            'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1',
            'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0'
        ]);
    }

    public function setLegalEntityCapabilities(string $uuid, array $document_types_and_processes): bool
    {
        if (count($document_types_and_processes) % 2 != 0) {
            throw new \Exception('One process per document type is required');
        }

        $enabled = count($document_types_and_processes) > 0;
        $capabilities = [];

        for ($i = 0; $i < count($document_types_and_processes); $i += 2) {
            $capabilities[] = [
                'documentTypeScheme' => 'busdox-docid-qns',
                'documentType' => $document_types_and_processes[$i],
                'processScheme' => 'cenbii-procid-ubl',
                'process' => $document_types_and_processes[$i + 1]
            ];
        }

        $body = [
            'enabled' => $enabled,
            'capabilities' => $capabilities
        ];

        $response = $this->prepareRequest()
            ->put(Constants::BASE_URL.'/legal-entities/'.$uuid.'/smp', $body);

        if ($response->successful()) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Invoice is XML UBL 2.1 (http://docs.oasis-open.org/ubl/os-UBL-2.1/UBL-2.1.html#T-INVOICE)
     * Response returns the uuid for the created invoice
     * More information:
     * 1) https://docs.acubeapi.com/documentation/peppol/peppol/tag/Invoice/#tag/Invoice/operation/post_outgoing_ublInvoiceCollection
     * 2) https://docs.acubeapi.com/documentation/peppol/manage-documents/sending-document/
     */
    public function sendInvoice(string $ubl): ?string
    {
        $response = $this->prepareRequest()
            ->withBody($ubl, 'application/xml')
            ->post(Constants::BASE_URL.'/invoices/outgoing/ubl');

        if ($response->successful()) {
            return $response['uuid'];
        }
        else {
            echo $response->body();
            throw new \Exception($response->body());
            return null;
        }
    }

    public function addIncomingInvoice(string $ubl): ?string
    {
        $response = $this->prepareRequest()
            ->withBody($ubl, 'application/xml')
            ->post(Constants::BASE_URL.'/invoices/incoming/ubl');

        if ($response->successful()) {
            return $response['uuid'];
        }
        else {
            return null;
        }
    }

    public function getInvoice(string $uuid): ?string
    {
        $response = $this->prepareRequest()
            ->get(Constants::BASE_URL.'/invoices/'.$uuid.'/source');

        if ($response->successful()) {
            return $response['uuid'];
        }
        else {
            return null;
        }
    }

    /**
     * Reads the received data and returns the id and type of the received document if any.
     * More info: https://docs.acubeapi.com/documentation/peppol/
     */
    public function onDataReceivedFromWebHook($body): array
    {
        return $body['success'] ?
                ['id' => $body['document_id'], 'type' => $body['document_type']] :
                ['id' => null, 'type' => null];
    }

    private function prepareRequest()
    {
        return Http::withToken($this->authentication->getValidToken())
            ->retry(3, 100, function ($exception, $request) {
                if ($exception instanceof ConnectionException) {
                    return true;
                }

                if ($exception instanceof RequestException && $exception->response->status() == 401) {
                    $new_token = $this->authentication->generateToken();
                 
                    if ($new_token == null) {
                        return false;
                    }
    
                    $request->withToken($new_token);

                    return true;
                }
                
                return false;
            }, throw: false)
            ->accept('application/json');
    }

}