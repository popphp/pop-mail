pop-mail
========

[![Build Status](https://github.com/popphp/pop-mail/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-mail/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-mail)](http://cc.popphp.org/pop-mail/)

* [Overview](#overview)
* [Install](#install)
* [Messages](#messages)
* [Mailer](#mailer)
* [Transports](#transports)
  - [Mailgun](#mailgun)
  - [Sendgrid](#sendgrid)
  - [Office 365](#office-365)
  - [AWS SES](#aws-ses)
  - [Google](#google)
  - [SMTP](#smtp)
  - [Sendmail](#sendmail)
* [Clients](#clients)
  - [Office 365 Client](#office-365-client)
  - [Google Client](#google-client)
  - [IMAP/POP3](#imappop3)
* [Mail Queues](#mail-queues)
* [Saving Mail](#saving-mail)

Overview
--------
`pop-mail` is the main mail component for the Pop PHP Framework. It provides a robust set of features to manage the
many aspects of sending and receiving mail over the internet. It provides functionality for the following:

- **Messages**
  - Create mail messages with multiple mime types and attachments
  - Save mail messages to be sent later
- **Mail Transports**
  - Manage sending mail messages from a server-to-server application connection*
  - Send emails to a queue of recipients, with individual message customization
  - The available mail transports are interchangeable:
    - **Mailgun Transport**: utilizes the Mailgun API
    - **Sendgrid Transport**: utilizes the Sendgrid API
    - **Office 365 Transport**: utilizes the Office 365 Mail API
    - **AWS SES Transport**: utilizes the AWS SDK
    - **Google Transport**: utilizes the Gmail API
    - **SMTP Transport**: utilizes a standard SMTP connection
    - **Sendmail Transport**: utilizes a basic Sendmail connection 
- **Mail Clients**
  - Manage mailboxes, their messages, data, attachments and statuses from a server-to-server application connection*
    - **Office 365 Client**: utilizes the Office 365 Mail API
    - **Google Client**: utilizes the Gmail API
    - **IMAP/POP3 Client**: utilizes a standard IMAP or POP3 connection
      *(for security purposes, IMAP & POP3 are not as widely supported anymore and their usage
      has been deprecated in some popular enterprise mail platforms)*

\* **NOTE:** - The main use-case for this component is that of a server-to-server application connection. This means
that the component would be used with an application that has been granted the appropriate access and permissions to
act on behalf of the user or users whose emails are being managed by the application. This component is not geared
towards an individual user mail application use-case. Please refer to the online documentation, guidelines and polices
for whichever mail platforms to which you are attempting to connect your application using this component. Please take
care in granting access and assigning permissions to your application instance. Always follow the recommended security
policies and guidelines of your chosen mail platform.  

`pop-mail` is a component of the [Pop PHP Framework](http://www.popphp.org/).

Install
-------

Install `pop-mail` using Composer.

    composer require popphp/pop-mail

Or, require it in your composer.json file

    "require": {
        "popphp/pop-mail" : "^4.0.0"
    }

Messages
--------

Create a simple text mail message:

```php
use Pop\Mail\Message;

$message = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');
```

Create a mail message with both text and HTML parts:

```php
use Pop\Mail\Message;

$message = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->addText('Hello World! This is a text body!');
$message->addHtml('<html><body><h1>Hello World!</h1><p>This is an HTML body!</p></body></html>');
```

Create a mail message with a file attachment:

```php
use Pop\Mail\Message;

$message = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');
$message->attachFile(__DIR__ . '/image.jpg');
```

Create a mail message with a file attachment from a stream of file contents:

```php
use Pop\Mail\Message;

$message = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');
$message->attachFileFromStream($fileContents, 'filename.pdf');
```

Mailer
------

Once a message object is created, it can be passed to a mailer object to be sent.
The mailer object will require a transport object to perform the required tasks
to send the message through the provided mail platform.

```php
use Pop\Mail\Message;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$transport = new Sendmail();
$mailer    = new Mailer($transport);
$message   = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');

$mailer->send($message);
```

Transports
----------

### Mailgun

The Mailgun transport requires an `api_url` and `api_key`. The API key is obtained from the
Mailgun administration portal. The Mailgun API URL is typically a string comprised of your
approved mail domain, for example:

```text
https://api.mailgun.net/v3/YOUR_MAIL_DOMAIN/messages
```

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Mailgun;

$mailgunOptions = [
    'api_url' => 'MAILGUN_API_URL',
    'api_key' => 'MAILGUN_API_KEY',
]
$transport = new Mailgun($mailgunOptions);
$mailer    = new Mailer($transport);
```

### Sendgrid

The Sendgrid transport requires an `api_url` and `api_key`. These values are obtained from the
Sendgrid administration portal.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendgrid;

$sendgridOptions = [
    'api_url' => 'SENDGRID_API_URL',
    'api_key' => 'SENDGRID_API_KEY',
]
$transport = new Sendgrid($sendgridOptions);
$mailer    = new Mailer($transport);
```

### Office 365

The Office 365 transport requires a few more configuration options that are obtained from the approved
application within the Office 365 administration portal. You will need the following:

- Client ID
- Client Secret
- Scope (This is typically something like `https://graph.microsoft.com/.default`)
- Tenant ID
- Account ID (This is typically the `object_id` of the user mailbox that is being used)

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Office365;

$transport = new Office365();
$transport->createClient([
    'client_id'     => 'O365_CLIENT_ID',
    'client_secret' => 'O365_CLIENT_SECRET',
    'scope'         => 'O365_SCOPE',
    'tenant_id'     => 'O365_TENANT_ID',
    'account_id'    => 'O365_ACCOUNT_ID',
]);

$mailer = new Mailer($transport);
```

### AWS SES

The AWS SES transport requires a `key` and `secret` that are obtained from the AWS SES admin console.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Ses;

$sesOptions = [
    'key'    => 'AWS_SES_KEY',
    'secret' => 'AWS_SES_SECRET',
]
$transport = new Ses($sesOptions);
$mailer    = new Mailer($transport);
```

### Google

The Google transport requires a number of configuration steps to be performed in the Google administration
portal and cloud console. This includes setting up the approved application as a `service account` and its
necessary requirements. When that is complete, you should be prompted to download a `JSON` file with
the appropriate credentials and data for your application:

```json
{
  "type": "service_account",
  "project_id": "PROJECT_ID",
  "private_key_id": "PRIVATE_KEY_ID",
  "private_key": "PRIVATE_KEY",
  "client_email": "CLIENT_EMAIL",
  "client_id": "CLIENT_ID",
  "auth_uri": "AUTH_URI",
  "token_uri": "TOKEN_URI",
  "auth_provider_x509_cert_url": "AUTH_PROVIDER",
  "client_x509_cert_url": "CLIENT_CERT_URL",
  "universe_domain": "UNIVERSE_DOMAIN"
}
```

From there, you pass the `JSON` file directly into Google transport object, along with the user email being used:

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Google;

$transport = new Google();
$transport->createClient('my-google-app-config.json', 'me@domain.com');

$mailer = new Mailer($transport);
```

### SMTP

The SMTP transport requires the standard configuration parameters for a typical SMTP connection:

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Smtp;

$smtpOptions = [
    'host'       => 'SMTP_HOST_DOMAIN',
    'port'       => 'SMTP_PORT',
    'username'   => 'SMTP_USERNAME',
    'password'   => 'SMTP_PASSWORD',
    'encryption' => 'SMTP_ENCRYPTION'
];

$transport = new Smtp($smtpOptions);
$mailer    = new Mailer($transport);
```

### Sendmail

Sendmail is the most basic transport. It is not used very often and is not recommended, but could be utilized
within testing and dev environments. It leverages the basic `sendmail` application running on the server, so
it is required that it be set up and configured properly on the server and within PHP for use with PHP's `mail`
function. If needed, you can pass a string of `$params` into the constructor that will be passed on to the
`mail` function call.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$transport = new Sendmail();
$mailer    = new Mailer($transport);
```

Clients
-------

### Office 365 Client

### Google Client

### IMAP/POP3

Mail Queues
-----------

Saving Mail
-----------

