# CYB
PHP code to Connect Your Books

Under construction.

This gateway will let you connect various data sources and sinks for bookkeeping data in the domains of:
* time tracking
* issue tracking
* invoicing
* mutual credit networks
* budget tracking

The name CYB stands for "Connect Your Books".

See [Federated Bookkeeping](https://federatedbookkeeping.org) for more info on the vision behind it.

## Usage

### for developing on local machine:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh dev pull
./run-docker.sh dev up --detach
```

### for developing on Gitpod:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh gitpod pull
./run-docker.sh gitpod up --detach
```

### for deploying on VPS:
```sh
cd cyb-app
chmod u+x ./run-docker.sh
./run-docker.sh prod pull
./run-docker.sh prod up --detach
```

## Development
### How to add a new connector
1. Pick a name for your connector. Must be all lowercase and url safe. Ex. time_mld, github
2. Create a new folder under `app/Connectors` and name it in the studly form of your connector name. Ex. TimeMld, Github
3. Put an implementation of `\app\Core\Connector` in the newly created folder. Ex. GithubConnector.php
4. Modify `\app\Core\ApplicationManager::getConnectors` and add an instance of your Connector to it.

### Routes
In order to support custom routes for your connector, create a folder named `routes` under your connector's root folder. Then you can create `api.php` and `web.php` files there to define your routes. The newly added routes will be under `/api/connector/{connector name}` and `/connector/{connector name}` respectively. And the routes can be addressed under the name `connector.{connector name}`.

### Returning views for your connector web routes
Suggested approach is to create a `resources\views` folder under your connector's root folder. Then you can reference those views by using the View facade (`Illuminate\Support\Facades\View`) and calling `View::file` method with the full address to your view file.

# Let's Peppol
Let's peppol is a CYB connector that provides free access to the PEPPOL network.

## How to integrate
In order to take advantage of Let's Peppol in your software you have to create a CYB connector for `invoice` data type. This connector should be included in the main running CYB instance. Then for each user, go through the following steps:

1. Call `POST /api/register` with `name`, `email` and `password` in the request body.
2. Call `POST /api/generateToken` with `email` and `password` to get a permanent bearer authorization token for all the other calls.
3. Call `POST /api/connector/lets_peppol/identity` with `name`, `address`, `city`, `region`, `country` code, `zip` in the request body. You will get the created `Identity` object in the response. You'll need to store the Identity id.
4. Keep calling `GET /api/connector/lets_peppol/identity/{identity_id}` to check the `kyc_status`. `0` means pending approval, `1` means rejected and `2` means approved. Approval is currently a manual process that takes time. After being approved, you can retrieve `identifier_scheme` and `identifier_value` from the identity object.
5. After getting approved, call `GET /api/authentication?app_code_name=lets_peppol&app_user_id={identity_id}` to get the relevant `Authentication` object for the created Let's Peppol Identity and store its id.
6. Call `POST /api/read/{authentication_id}/invoice` with `read` set as `true` in the request body to enable receiving invoices for Let's Peppol.
7. Call `POST /api/write/{authentication_id}/invoice` with `write` set as `true` in the request body to enable auto sending of out going invoices?
8. You can always call `GET /api/function/{auth_id}/invoice` to check the `read` and `write` status of the authentication.
9. Create an authentication in your connector for the user. To do this you'll need to define your own API endpoints and eventually call `ApplicationManager::createAuthentication($auth_info)`.
10. Repeat steps 6 and 7 for your application's authentication to enable read and write functionalities.
11. You can always call `POST /api/connector/lets_peppoo/message/{identity_id}` with an UBL as the request body to directly send invoices through Let's Peppol. If `read` is turned on, you'll receive the event upon delivery of the invoice.

## UBL
A sample UBL invoice looks like the following:
``` xml
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2">
	<cbc:UBLVersionID>2.1</cbc:UBLVersionID>
	<cbc:CustomizationID>urn:cen.eu:en16931:2017#compliant#urn:fdc:peppol.eu:2017:poacc:billing:3.0</cbc:CustomizationID>
	<cbc:ProfileID>urn:fdc:peppol.eu:2017:poacc:billing:01:1.0</cbc:ProfileID>
	<cbc:ID>1234</cbc:ID>
	<cbc:IssueDate>2022-09-06</cbc:IssueDate>
	<cbc:InvoiceTypeCode>380</cbc:InvoiceTypeCode>
	<cbc:Note>invoice note</cbc:Note>
	<cbc:DocumentCurrencyCode>EUR</cbc:DocumentCurrencyCode>
	<cbc:AccountingCost>4217:2323:2323</cbc:AccountingCost>
	<cbc:BuyerReference>BUYER_REF</cbc:BuyerReference>
	<cac:InvoicePeriod>
		<cbc:StartDate>2022-09-06</cbc:StartDate>
	</cac:InvoicePeriod>
	<cac:OrderReference>
		<cbc:ID>5009567</cbc:ID>
		<cbc:SalesOrderID>tRST-tKhM</cbc:SalesOrderID>
	</cac:OrderReference>
	<cac:AccountingSupplierParty>
		<cac:Party>
			<cbc:EndpointID schemeID="9915">asdffbddsf</cbc:EndpointID>
			<cac:PartyIdentification>
				<cbc:ID>99887766</cbc:ID>
			</cac:PartyIdentification>
			<cac:PartyName>
				<cbc:Name>PonderSource</cbc:Name>
			</cac:PartyName>
			<cac:PostalAddress>
				<cbc:StreetName>Lisk Center Utreht</cbc:StreetName>
				<cbc:AdditionalStreetName>De Burren</cbc:AdditionalStreetName>
				<cbc:CityName>Utreht</cbc:CityName>
				<cbc:PostalZone>3521</cbc:PostalZone>
				<cac:Country>
					<cbc:IdentificationCode>NL</cbc:IdentificationCode>
				</cac:Country>
			</cac:PostalAddress>
			<cac:PartyTaxScheme>
				<cbc:CompanyID>NL123456789</cbc:CompanyID>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:PartyTaxScheme>
			<cac:PartyLegalEntity>
				<cbc:RegistrationName>PonderSource</cbc:RegistrationName>
				<cbc:CompanyID>NL123456789</cbc:CompanyID>
			</cac:PartyLegalEntity>
		</cac:Party>
	</cac:AccountingSupplierParty>
	<cac:AccountingCustomerParty>
		<cac:Party>
			<cbc:EndpointID schemeID="9915">phase4-test-sender</cbc:EndpointID>
			<cac:PartyIdentification>
				<cbc:ID>9988217</cbc:ID>
			</cac:PartyIdentification>
			<cac:PartyName>
				<cbc:Name>Client Company Name</cbc:Name>
			</cac:PartyName>
			<cac:PostalAddress>
				<cbc:StreetName>Lisk Center Utreht</cbc:StreetName>
				<cbc:AdditionalStreetName>De Burren</cbc:AdditionalStreetName>
				<cbc:CityName>Utreht</cbc:CityName>
				<cbc:PostalZone>3521</cbc:PostalZone>
				<cac:Country>
					<cbc:IdentificationCode>NL</cbc:IdentificationCode>
				</cac:Country>
			</cac:PostalAddress>
			<cac:PartyTaxScheme>
				<cbc:CompanyID>BE123456789</cbc:CompanyID>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:PartyTaxScheme>
			<cac:PartyLegalEntity>
				<cbc:RegistrationName>Client Company Name</cbc:RegistrationName>
				<cbc:CompanyID>Client Company Registration</cbc:CompanyID>
			</cac:PartyLegalEntity>
			<cac:Contact>
				<cbc:Name>Client name</cbc:Name>
				<cbc:Telephone>908-99-74-74</cbc:Telephone>
			</cac:Contact>
		</cac:Party>
	</cac:AccountingCustomerParty>
	<cac:Delivery>
		<cbc:ActualDeliveryDate>2022-09-06</cbc:ActualDeliveryDate>
		<cac:DeliveryLocation>
			<cac:Address>
				<cbc:StreetName>Delivery street 2</cbc:StreetName>
				<cbc:AdditionalStreetName>Building 56</cbc:AdditionalStreetName>
				<cbc:CityName>Utreht</cbc:CityName>
				<cbc:PostalZone>3521</cbc:PostalZone>
				<cac:Country>
					<cbc:IdentificationCode>NL</cbc:IdentificationCode>
				</cac:Country>
			</cac:Address>
		</cac:DeliveryLocation>
	</cac:Delivery>
	<cac:PaymentMeans>
		<cbc:PaymentMeansCode>31</cbc:PaymentMeansCode>
		<cbc:PaymentID>our invoice 1234</cbc:PaymentID>
		<cac:PayeeFinancialAccount>
			<cbc:ID>NL00RABO0000000000</cbc:ID>
			<cbc:Name>Customer Account Holder</cbc:Name>
			<cac:FinancialInstitutionBranch>
				<cbc:ID>RABONL2U</cbc:ID>
			</cac:FinancialInstitutionBranch>
		</cac:PayeeFinancialAccount>
	</cac:PaymentMeans>
	<cac:PaymentTerms>
		<cbc:Note>30 days net</cbc:Note>
	</cac:PaymentTerms>
	<cac:TaxTotal>
		<cbc:TaxAmount currencyID="EUR">2.10</cbc:TaxAmount>
		<cac:TaxSubtotal>
			<cbc:TaxableAmount currencyID="EUR">10.00</cbc:TaxableAmount>
			<cbc:TaxAmount currencyID="EUR">2.10</cbc:TaxAmount>
			<cac:TaxCategory>
				<cbc:ID>S</cbc:ID>
				<cbc:Percent>21.00</cbc:Percent>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:TaxCategory>
		</cac:TaxSubtotal>
	</cac:TaxTotal>
	<cac:LegalMonetaryTotal>
		<cbc:LineExtensionAmount currencyID="EUR">10.00</cbc:LineExtensionAmount>
		<cbc:TaxExclusiveAmount currencyID="EUR">10.00</cbc:TaxExclusiveAmount>
		<cbc:TaxInclusiveAmount currencyID="EUR">12.10</cbc:TaxInclusiveAmount>
		<cbc:AllowanceTotalAmount currencyID="EUR">0.00</cbc:AllowanceTotalAmount>
		<cbc:PayableAmount currencyID="EUR">12.10</cbc:PayableAmount>
	</cac:LegalMonetaryTotal>
	<cac:InvoiceLine>
		<cbc:ID>0</cbc:ID>
		<cbc:InvoicedQuantity unitCode="C62">1.00</cbc:InvoicedQuantity>
		<cbc:LineExtensionAmount currencyID="EUR">10.00</cbc:LineExtensionAmount>
		<cac:InvoicePeriod>
			<cbc:StartDate>2022-09-06</cbc:StartDate>
		</cac:InvoicePeriod>
		<cac:Item>
			<cbc:Description>Product Description</cbc:Description>
			<cbc:Name>Product Name</cbc:Name>
			<cac:ClassifiedTaxCategory>
				<cbc:ID>S</cbc:ID>
				<cbc:Percent>21.00</cbc:Percent>
				<cac:TaxScheme>
					<cbc:ID>VAT</cbc:ID>
				</cac:TaxScheme>
			</cac:ClassifiedTaxCategory>
		</cac:Item>
		<cac:Price>
			<cbc:PriceAmount currencyID="EUR">10.00</cbc:PriceAmount>
			<cbc:BaseQuantity unitCode="C62">1.00</cbc:BaseQuantity>
		</cac:Price>
	</cac:InvoiceLine>
</Invoice>
```