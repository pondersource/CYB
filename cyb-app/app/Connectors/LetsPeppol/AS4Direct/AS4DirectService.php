<?php

namespace App\Connectors\LetsPeppol\AS4Direct;

use App\Connectors\LetsPeppol\KeyStore;
use App\Connectors\LetsPeppol\LetsPeppolService;
use App\Connectors\LetsPeppol\Models\Identity;
use App\Connectors\LetsPeppol\Models\Message;
use App\Core\Helper;
use App\Core\Settings;
use App\Models\Authentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use JMS\Serializer\SerializerBuilder;
use OCA\PeppolNext\PonderSource\EBBP\MessagePartNRInformation;
use OCA\PeppolNext\PonderSource\EBMS\CollaborationInfo;
use OCA\PeppolNext\PonderSource\EBMS\MessageInfo;
use OCA\PeppolNext\PonderSource\EBMS\Messaging;
use OCA\PeppolNext\PonderSource\EBMS\PartInfo;
use OCA\PeppolNext\PonderSource\EBMS\Party;
use OCA\PeppolNext\PonderSource\EBMS\PartyId;
use OCA\PeppolNext\PonderSource\EBMS\PartyInfo;
use OCA\PeppolNext\PonderSource\EBMS\PayloadInfo;
use OCA\PeppolNext\PonderSource\EBMS\Property;
use OCA\PeppolNext\PonderSource\EBMS\Receipt;
use OCA\PeppolNext\PonderSource\EBMS\Service;
use OCA\PeppolNext\PonderSource\EBMS\SignalMessage;
use OCA\PeppolNext\PonderSource\EBMS\UserMessage;
use OCA\PeppolNext\PonderSource\Envelope\Body;
use OCA\PeppolNext\PonderSource\Envelope\Envelope;
use OCA\PeppolNext\PonderSource\Envelope\Header;
use OCA\PeppolNext\PonderSource\SBD\DocumentIdentification;
use OCA\PeppolNext\PonderSource\SBD\Identifier;
use OCA\PeppolNext\PonderSource\SBD\Receiver;
use OCA\PeppolNext\PonderSource\SBD\Scope;
use OCA\PeppolNext\PonderSource\SBD\Sender;
use OCA\PeppolNext\PonderSource\SBD\StandardBusinessDocument;
use OCA\PeppolNext\PonderSource\SBD\StandardBusinessDocumentHeader;
use OCA\PeppolNext\PonderSource\WSSec\CanonicalizationMethod\C14NExclusive;
use OCA\PeppolNext\PonderSource\WSSec\DigestMethod\SHA256;
use OCA\PeppolNext\PonderSource\WSSec\DSigReference;
use OCA\PeppolNext\PonderSource\WSSec\Security;
use OCA\PeppolNext\PonderSource\WSSec\SignatureMethod\RsaSha256;
use OCA\PeppolNext\PonderSource\WSSec\Transform;
use phpseclib3\Crypt\{RSA, Random};

Helper::include_once(__DIR__.'/../PonderSource');

class AS4DirectService
{
	private KeyStore $keyStore;

	public function __construct()
    {
        $this->keyStore = new KeyStore();
    }

    public function getInfo(): array
    {
		$info = $this->keyStore->getInfo();

		$info['endpoint'] = route('connector.lets_peppol.as4-direct.endpoint');

        return $info;
    }

    public function endpointMessage(Request $request)
    {
        list($raw_envelope, $raw_payload) = $this->getEnvelopeAndPayload($request);
        $serializer = \JMS\Serializer\SerializerBuilder::create()->build();
		$envelope = $serializer->deserialize($raw_envelope, 'OCA\PeppolNext\PonderSource\Envelope\Envelope::class', 'xml');

		list($sender, $receiver) = $envelope->getHeader()->getMessaging()->getUserMessage()->getPeppolSenderAndReceiver();

		if ($sender == null || $receiver == null) {
            throw new \Exception('Sender or receiver not specified');
		}

		$sender_identity = null;

        try {
            $sender_identity = Identity::query()
                    ->where('identifier_value', $sender->getValue())
                    ->where('identifier_scheme', $sender->getType())
                    ->first();
        } catch (\Exception $e) {
			throw new \Exception('Sender not recognized');
        }

		$info = $this->keyStore->getInfo();

		$receiver_identity = null;

		if ($receiver->getType() === $info['identity_scheme'] && $receiver->getValue() === $info['identity_value']) {
			// We are the receiver
		}
		else {
			try {
				$receiver_identity = Identity::query()
						->where('identifier_value', $receiver->getValue())
						->where('identifier_scheme', $receiver->getType())
						->first();
			} catch (\Exception $e) {
				throw new \Exception('Receiver not recognized');
			}
		}

		$private_key = $this->keyStore->getPrivateKey();

		$decrypted_payload = $envelope->getHeader()->decodePayload($raw_payload, $private_key);
		$sbd = $serializer->deserialize($decrypted_payload,'OCA\PeppolNext\PonderSource\SBD\StandardBusinessDocument::class', 'xml');
		$invoice = $sbd->getInvoice();

		$sender_public_key = KeyStore::publicKeyFromString($sender_identity['as4direct_public_key']);
		$verifyResult = $envelope->getHeader()->getSecurity()->getSignature()->verify($envelope, $decrypted_payload, $sender_public_key);

		if (!$verifyResult) {
			throw new \Exception('Signature verification failed.');
		}

		$message_handled = false;

		if (isset($receiver_identity)) {
			if (!empty($receiver_identity['auth_id'])) {
				$message = new Message();
				$message['registrar'] = LetsPeppolService::REGISTRAR_AS4_DIRECT;
				$message['reference'] = $invoice->getId();
				$message['type'] = Message::TYPE_INVOICE;
				$message['direction'] = Message::DIRECTION_INCOMING;
				$message['identity_id'] = $receiver_identity['id'];
				$message['receive_time'] = time();

				$file_name = uniqid('message-');
				file_put_contents(Message::STORAGE_BASE_PATH.$file_name, $ubl);
				$message['file_name'] = $file_name;
				
				$message->save();

				try {
					$auth = Authentication::query()
						->where('id', $identity['auth_id'])
						->first();
	
					ApplicationManager::onNewUpdate($auth, 'invoice');
				} catch (\Exception $e) {
					throw new \Exception('Failed to notify the new message.');
				}

				$message_handled = true;
			}
			else if (!empty($receiver_identity['as4direct_endpoint'])
					&& !empty($receiver_identity['as4direct_public_key'])
					&& !empty($receiver_identity['as4direct_certificate'])) {
				$successful = $this->sendAS4Message($invoice, $receiver_identity, $info, $private_key);

				if (!$successful) {
					throw new \Exception('Could not verify redirect response.');
				}
				
				$message_handled = true;
			}
		}

		if (!$message_handled) {
			$letsPeppolService = new LetsPeppolService();

			if (!$letsPeppolService->sendMessage($sender_identity['id'], $decrypted_payload)) {
				throw new \Exception('Failed to send the message in the peppol network');
			}
		}

		// Return success response
		// FIXME: are there really supposed to be two message ID's? One for the request and one for the response?
		$theirMsgId = $envelope->getHeader()->getMessaging()->getUserMessage()->getMessageInfo()->getMessageId();
		$ourMsgId = uniqid('letspeppol-msg-');
		$ourBodyId = uniqid('id-');
			
		$nonRepudiationInformation = [];
			
		foreach ($envelope->getHeader()->getSecurity()->getSignature()->getSignedInfo()->getReferences() as $reference) {
				$nonRepudiationInformation[] = (new MessagePartNRInformation())->addReference($reference);
		}

		$cert = $this->keyStore->getCertificate();
		
		return $this->generateResponse($theirMsgId, $ourMsgId, $ourBodyId, $nonRepudiationInformation, $private_key, $cert);
    }

	private function sendAS4Message(Invoice $invoice, Identity $receiver_identity, array $info, $private_key)
	{
		$boundry = '----=_Part_'.uniqid();
		list($body, $raw_envelope, $raw_payload) = $this->prepareBody(
				$info['identity_scheme'],
				$info['identity_value'],
				$receiver_identity['identifier_scheme'],
				$receiver_identity['identifier_value'],
				$invoice,
				$private_key,
				KeyStore::certificateFromString($receiver_identity['as4direct_certificate']),
				$boundry);

		$response = Http::withHeaders([
				'Message-Id' => '<'.uniqid().'>',
				'MIME-Version' => '1.0'
			])
			->withBody($body, "multipart/related;    boundary=\"$boundry\";    type=\"application/soap+xml\"; charset=UTF-8")
			->post($receiver_identity['as4direct_endpoint']);

		$responseBody = $response->body();

		$serializer = SerializerBuilder::create()->build();
		$response = $serializer->deserialize($responseBody, 'OCA\PeppolNext\PonderSource\Envelope\Envelope::class', 'xml');

		$receiver_public_key = KeyStore::publicKeyFromString($receiver_identity['as4direct_public_key']);
		$verifyResult = $response->getHeader()->getSecurity()->getSignature()->verify($response, null, $receiver_public_key);

		return $verifyResult;
	}

	private function prepareBody($s_scheme, $s_id, $r_scheme, $r_id, $invoice, $s_key, $r_cert, $boundry)
	{
		// Prepare the request
		$messagingId = uniqid('letspeppol-msg-');
		$messageId = uniqid().'@letspeppol';
		$bodyId = uniqid('id-');
		$payloadId = uniqid('letspeppol-att-').'@cid';

		$r_cn = $r_cert->getDNProp('CN');

		$envelope = $this->prepareEnvelope($messagingId, $messageId, $s_scheme, $s_id, $r_scheme, $r_id, $r_cn, $payloadId, $bodyId);
		list($raw_payload, $payload) = $this->preparePayload($envelope, $s_scheme, $s_id, $r_scheme, $r_id, $invoice, $messagingId, $bodyId, $payloadId, $s_key, $r_cert);

		$serializer = SerializerBuilder::create()->build();
		$c14ne = new Transform("http://www.w3.org/2001/10/xml-exc-c14n#");  //C14NExcTransform();
		$serializedEnvelope = $c14ne->transform($serializer->serialize($envelope, 'xml'));
		$serializedEnvelope = str_replace("\n", '', $serializedEnvelope);
		$serializedEnvelope = str_replace("  ", '', $serializedEnvelope);
		
		$body = "\r\n--$boundry\r\nContent-Type: application/soap+xml;charset=UTF-8\r\nContent-Transfer-Encoding: binary\r\n\r\n$serializedEnvelope\r\n--$boundry\r\nContent-Type: application/octet-stream\r\nContent-Transfer-Encoding: binary\r\nContent-Description: Attachment\r\nContent-ID: <$payloadId>\r\n\r\n$payload\r\n--$boundry--\r\n";

		return [$body, $serializedEnvelope, $raw_payload];
	}

	private function prepareEnvelope($messagingId, $messageId, $s_scheme, $s_id, $r_scheme, $r_id, $r_cn, $payloadId, $bodyId)
	{
		return new Envelope(
			new Header(
				new Security(

				),
				new Messaging(new UserMessage(
					new MessageInfo(new \DateTime(), $messageId),
					new PartyInfo(
						new Party(new PartyId(KeyStore::NAME, 'urn:fdc:peppol.eu:2017:identifiers:ap'), 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/initiator'),
						new Party(new PartyId($r_cn, 'urn:fdc:peppol.eu:2017:identifiers:ap'), 'http://docs.oasis-open.org/ebxml-msg/ebms/v3.0/ns/core/200704/responder')
						),
					new CollaborationInfo(
						'urn:fdc:peppol.eu:2017:agreements:tia:ap_provider',
						new Service($value='urn:fdc:peppol.eu:2017:poacc:billing:01:1.0', $serviceType='cenbii-procid-ubl'),
						'busdox-docid-qns::urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1',
						'phase4@Conv-3221508681736967991'
					),
					[
						new Property($s_id, 'originalSender', $s_scheme),
						new Property($r_id, 'finalRecipient', $r_scheme)
					],
					new PayloadInfo(new PartInfo(
						'cid:'.$payloadId,
						[
							new Property('application/xml','MimeType'),
							new Property('application/gzip','CompressionType')
						]
					))
				), null, $messagingId)
			),
			new Body($bodyId)
		);
	}

	private function preparePayload($envelope, $s_scheme, $s_id, $r_scheme, $r_id, $invoice, $messagingId, $bodyId, $payloadId, $s_key, $r_cert) {
		$payloadKey = Random::string(32);

		$sha256 = new SHA256();
		$c14ne = new Transform("http://www.w3.org/2001/10/xml-exc-c14n#");  //C14NExcTransform();

		$serializer = SerializerBuilder::create()->build();
		$serializedMessaging = $serializer->serialize($envelope->getHeader()->getMessaging(), 'xml');
		$serializedBody = $serializer->serialize($envelope->getBody(), 'xml');

		$instanceIdentifier = uniqid();
		$standardBusinessDocument = new StandardBusinessDocument(new StandardBusinessDocumentHeader(
			'1.0',
			new Sender(new Identifier($s_scheme, $s_id)),
			new Receiver(new Identifier($r_scheme, $r_id)),
			new DocumentIdentification(
				'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
				'2.1',
				$instanceIdentifier,
				'Invoice',
				new \DateTime()
			),
			[
				new Scope('DOCUMENTID', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2::Invoice##urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0::2.1', 'busdox-docid-qns'),
				new Scope('PROCESSID', 'urn:fdc:peppol.eu:2017:poacc:billing:01:1.0', 'cenbii-procid-ubl')
			]
		), $invoice);
		$raw_payload = $serializer->serialize($standardBusinessDocument, 'xml');
		$raw_payload = $c14ne->transform($raw_payload);
		$raw_payload = str_replace("\n", '', $raw_payload);
		$raw_payload = str_replace("  ", '', $raw_payload);

		$payload = gzencode($raw_payload);
		$references = [
			new DSigReference("#$messagingId", $serializedMessaging, [$c14ne], $sha256),
			new DSigReference("#$bodyId", $serializedBody, [$c14ne], $sha256),
			new DSigReference("cid:$payloadId", $payload, [new Transform('http://docs.oasis-open.org/wss/oasis-wss-SwAProfile-1.1#Attachment-Content-Signature-Transform')], $sha256)
		];

		$envelope->getHeader()->getSecurity()->generateSignature($s_key, $r_cert, $references, new C14NExclusive(), new RsaSha256(), $envelope);
		$payload = $envelope->getHeader()->getSecurity()->encryptData($payloadKey, $r_cert, "cid:$payloadId", $payload);
		return [$raw_payload, $payload];
	}

	private function generateResponse($theirMsgId, $ourMsgId, $ourBodyId, $nonRepudiationInformation, $private_key, $cert)
	{
		$response = new Envelope(
			new Header(
				new Security(

				),
				new Messaging(null, new SignalMessage(
					new MessageInfo(
						new \DateTime(),
						uniqid().'@letspeppol',
						$theirMsgId),
					new Receipt($nonRepudiationInformation),
					null
				), $ourMsgId)
			),
			new Body($ourBodyId)
		);

		$sha256 = new SHA256();
		$c14ne = new Transform("http://www.w3.org/2001/10/xml-exc-c14n#");  //C14NExcTransform();

		$serializer = SerializerBuilder::create()->build();
		$serializedMessaging = $serializer->serialize($response->getHeader()->getMessaging(), 'xml');
		$serializedMessaging = str_replace("  ", '', str_replace("\n", '', $serializedMessaging));
		$serializedBody = $serializer->serialize($response->getBody(), 'xml');
		$serializedBody = str_replace("  ", '', str_replace("\n", '', $serializedBody));

		$references = [
			new DSigReference("#$ourMsgId", $serializedMessaging, [$c14ne], $sha256),
			new DSigReference("#$ourBodyId", $serializedBody, [$c14ne], $sha256)
		];

		$response->getHeader()->getSecurity()->generateSignature($private_key, $cert, $references, new C14NExclusive(), new RsaSha256(), $response);

		$serializedCanonicalizedResponse = $c14ne->transform($serializer->serialize($response, 'xml'));
		$serializedCanonicalizedResponse = str_replace("\n", '', $serializedCanonicalizedResponse);
		$serializedCanonicalizedResponse = str_replace("  ", '', $serializedCanonicalizedResponse);

		return response($serializedCanonicalizedResponse, 200)
				->withHeaders([
					'Referrer-Policy' => 'strict-origin-when-cross-origin',
					'X-Frame-Options' => 'SAMEORIGIN',
					'X-Content-Type-Options' => 'nosniff',
					'X-XSS-Protection' => '1; mode=block',
					'Strict-Transport-Security' => 'max-age=3600;includeSubDomains',
					'Cache-Control' => 'no-cache, no-store, must-revalidate, proxy-revalidate',
					'Content-Type' => 'application/soap+xml;charset=utf-8',
					'Content-Disposition' => null
				]);
	}

    private function getEnvelopeAndPayload(Request $request)
    {
		$contentType = $request->header('Content-Type');
		$boundryStart = strpos($contentType, 'boundary="');
		$boundryEnd = strpos($contentType, '"', $boundryStart + 10);
		$boundry = substr($contentType, $boundryStart + 10, $boundryEnd - $boundryStart - 10);
		$boundryLength = strlen($boundry);
		$body = $request->getContent();
		$pointer = strpos($body, $boundry);
		$pointer = strpos($body, "\r\n\r\n", $pointer);
		$envelopeStart = $pointer + 4;
		$pointer = strpos($body, $boundry, $envelopeStart);
		$envelopeEnd = $pointer - 4;
		$envelope = substr($body, $envelopeStart, $envelopeEnd - $envelopeStart);
		$pointer = strpos($body, "\r\n\r\n", $pointer);
		$payloadStart = $pointer + 4;
		$pointer = strpos($body, $boundry, $payloadStart);
		$payloadEnd = $pointer - 4;
		$payload = substr($body, $payloadStart, $payloadEnd - $payloadStart);
		return [ $envelope, $payload ];
	}
}