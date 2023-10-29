pop-mail
========

[![Build Status](https://github.com/popphp/pop-mail/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-mail/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-mail)](http://cc.popphp.org/pop-mail/)

[![Join the chat at https://discord.gg/8DUdCr9x](https://www.popphp.org/assets/img/discord-chat.svg)](https://discord.gg/8DUdCr9x)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
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
  - Send the messages through the mailer object, or save mail messages to be sent later
  - Send the messages through a queue with multiple recipients
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

\* - **NOTE:** The main use-case for this component is that of a server-to-server application connection. This means
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

[Top](#pop-mail)

Quickstart
----------

**Example 1:** Sending a message via the Mailgun API

```php
use Pop\Mail\Message;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Mailgun;

// Create the transport and mailer objects
$transport = new Mailgun([
    'api_url' => 'MAILGUN_API_URL',
    'api_key' => 'MAILGUN_API_KEY',
]);
$mailer = new Mailer($transport);

// Create the message object
$message = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');

// Send the message
$mailer->send($message);
```

**Example 2:** Sending a message via an SMTP connection

```php
use Pop\Mail\Message;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Smtp;

$smtpOptions = [
    'host'       => 'SMTP_HOST_DOMAIN',
    'port'       => 'SMTP_PORT',
    'username'   => 'SMTP_USERNAME',
    'password'   => 'SMTP_PASSWORD',
    'encryption' => 'SMTP_ENCRYPTION'
];

// Create the transport and mailer objects
$transport = new Smtp($smtpOptions);
$mailer    = new Mailer($transport);

// Create the message object
$message   = new Message('My Message Subject');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');

// Send the message
$mailer->send($message);
```

[Top](#pop-mail)

Messages
--------

Message objects can be created and passed to a mailer object to be sent by an application. 

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

[Top](#pop-mail)

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

[Top](#pop-mail)

Transports
----------

The available transports all share the same interface and are interchangeable. However, they
each require different configuration that adheres to the specifications of their mail platform. 

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
];
$transport = new Mailgun($mailgunOptions);
$mailer    = new Mailer($transport);
```

[Top](#pop-mail)

### Sendgrid

The Sendgrid transport requires an `api_url` and `api_key`. These values are obtained from the
Sendgrid administration portal.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendgrid;

$sendgridOptions = [
    'api_url' => 'SENDGRID_API_URL',
    'api_key' => 'SENDGRID_API_KEY',
];
$transport = new Sendgrid($sendgridOptions);
$mailer    = new Mailer($transport);
```

[Top](#pop-mail)

### Office 365

The Office 365 transport requires a few more configuration options that are obtained for the approved
application from within the Office 365 administration portal. You will need the following:

- Client ID
- Client Secret
- Scope (This is typically something like `https://graph.microsoft.com/.default`)
- Tenant ID
- Account ID (This is typically the `object_id` of the user mailbox that is being used)

You can create an Office 365 transport object and then request and store the required token for future requests:

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

// Fetch the token and its expiration to be stored with your application for future use
$transport->requestToken();
$accessToken  = $transport->getToken();
$tokenExpires = $transport->getTokenExpires();

$mailer = new Mailer($transport);
```

When calling the Office 365 transport object at a later time, you can reuse the token (if it hasn't expired):

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

// Get the access token and its expiration from your application store
$transport->setToken($accessToken)
$transport->setTokenExpires($tokenExpires);

$mailer = new Mailer($transport);
```

If the token has expired, the transport object will automatically refresh it. At this point, you can fetch the
new token and its expiration from the transport object and store them.

[Top](#pop-mail)

### AWS SES

The AWS SES transport requires a `key` and `secret` that are obtained from the AWS SES admin console.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Ses;

$sesOptions = [
    'key'    => 'AWS_SES_KEY',
    'secret' => 'AWS_SES_SECRET',
];
$transport = new Ses($sesOptions);
$mailer    = new Mailer($transport);
```

[Top](#pop-mail)

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

You can pass the `JSON` file directly into Google transport object, along with the user email being used.
From there, you can request and store the required token for future requests:

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Google;

$transport = new Google();
$transport->createClient('my-google-app-config.json', 'me@domain.com');

// Fetch the token and its expiration to be stored with your application for future use
$transport->requestToken();
$accessToken  = $transport->getToken();
$tokenExpires = $transport->getTokenExpires();

$mailer = new Mailer($transport);
```

When calling the Google transport object at a later time, you can reuse the token (if it hasn't expired):

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Google;

$transport = new Google();
$transport->createClient('my-google-app-config.json', 'me@domain.com');

// Get the access token and its expiration from your application store
$transport->setToken($accessToken)
$transport->setTokenExpires($tokenExpires);

$mailer = new Mailer($transport);
```

If the token has expired, the transport object will automatically refresh it. At this point, you can fetch
the new token and its expiration from the transport object and store them.

[Top](#pop-mail)

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
within testing and dev environments. It leverages the `sendmail` application running on the server, so it is
required that it be set up and configured properly on the server and within PHP for use with PHP's `mail`
function. If needed, you can pass a string of `$params` into the constructor that will be passed on to the
`mail` function call.

```php
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$transport = new Sendmail();
$mailer    = new Mailer($transport);
```

[Top](#pop-mail)

Clients
-------

The available mail clients can be used for monitoring mailboxes and their message content from within an application.

### Office 365 Client

Like the Office 365 transport, the Office 365 clients requires a few configuration options that are obtained
for the approved application from within the Office 365 administration portal. You will need the following:

- Client ID
- Client Secret
- Scope (This is typically something like `https://graph.microsoft.com/.default`)
- Tenant ID
- Account ID (This is typically the `object_id` of the user mailbox that is being used)

You can create an Office 365 client object and then request and store the required token for future requests:

```php
use Pop\Mail\Client\Office365;

$office365 = new Office365();
$office365->createClient([
    'client_id'     => 'O365_CLIENT_ID',
    'client_secret' => 'O365_CLIENT_SECRET',
    'scope'         => 'O365_SCOPE',
    'tenant_id'     => 'O365_TENANT_ID',
    'account_id'    => 'O365_ACCOUNT_ID',
]);

// Fetch the token and its expiration to be stored with your application for future use
$office365->requestToken();
$accessToken  = $office365->getToken();
$tokenExpires = $office365->getTokenExpires();
```

When calling the Office 365 client object at a later time, you can reuse the token (if it hasn't expired):

```php
use Pop\Mail\Client\Office365;

$office365 = new Office365();
$office365->createClient([
    'client_id'     => 'O365_CLIENT_ID',
    'client_secret' => 'O365_CLIENT_SECRET',
    'scope'         => 'O365_SCOPE',
    'tenant_id'     => 'O365_TENANT_ID',
    'account_id'    => 'O365_ACCOUNT_ID',
]);

// Get the access token and its expiration from your application store
$office365->setToken($accessToken)
$office365->setTokenExpires($tokenExpires);
```

If the token has expired, the client object will automatically refresh it. At this point, you can fetch
the new token and its expiration from the client object and store them.

From there you can interact with the client to fetch messages and their content.

##### Get messages

```php
// Defaults to the Inbox and a limit of 10 messages. Returns an array of messages
$messages = $office365->getMessages();
```

Search for unread messages only, limit 5:

```php
$messages = $office365->getMessages('Inbox', ['unread' => true], 5);
```

##### Get a message

When the messages are returned, they will have any ID associated with them. Use that to get an individual message:

```php
// Returns an array of message data
$message = $office365->getMessage($messageId);
```

You can get the raw message as well:

```php
// Returns a string of the full message content
$message = $office365->getMessage($messageId, true);
```

##### Get a message's attachments

```php
// Returns an array of attachments
$attachments = $office365->getAttachments($messageId);
```

##### Get an attachment

When the message's attachments are returned, they will have any ID associated with them.
Use that to get an individual message attachment:

```php
// Returns an array of attachment data, including the attachment data contents
$attachment = $office365->getAttachment($messageId, $attachmentId);
```

[Top](#pop-mail)

### Google Client

Like the Google transport, the Google client requires a number of configuration steps to be performed in
the Google administration portal and cloud console. This includes setting up the approved application as
a `service account` and its necessary requirements. When that is complete, you should be prompted to
download a `JSON` file with the appropriate credentials and data for your application:

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

You can pass the `JSON` file directly into Google client object, along with the user email being used.
From there, you can request and store the required token for future requests:

```php
use Pop\Mail\Client\Google;

$google = new Google();
$google->createClient('my-google-app-config.json', 'me@domain.com');

// Fetch the token and its expiration to be stored with your application for future use
$google->requestToken();
$accessToken  = $google->getToken();
$tokenExpires = $google->getTokenExpires();
```

When calling the Google client object at a later time, you can reuse the token (if it hasn't expired):

```php
use Pop\Mail\Client\Google;

$google = new Google();
$google->createClient('my-google-app-config.json', 'me@domain.com');

// Get the access token and its expiration from your application store
$google->setToken($accessToken)
$google->setTokenExpires($tokenExpires);
```

If the token has expired, the client object will automatically refresh it. At this point, you can fetch
the new token and its expiration from the client object and store them.

From there you can interact with the client to fetch messages and their content.

##### Get messages

```php
// Defaults to the Inbox and a limit of 10 messages. Returns an array of messages
$messages = $google->getMessages();
```

Search for unread messages only, limit 5:

```php
$messages = $google->getMessages('Inbox', ['unread' => true], 5);
```

##### Get a message

When the messages are returned, they will have any ID associated with them. Use that to get an individual message:

```php
// Returns an array of message data
$message = $google->getMessage($messageId);
```

You can get the raw message as well:

```php
// Returns a string of the full message content
$message = $google->getMessage($messageId, true);
```

##### Get a message's attachments

```php
// Returns an array of attachments
$attachments = $google->getAttachments($messageId);
```

##### Get an attachment

When the message's attachments are returned, they will have any ID associated with them.
Use that to get an individual message attachment:

```php
// Returns the attachment data contents
$attachment = $google->getAttachment($messageId, $attachmentId);
```

[Top](#pop-mail)

### IMAP/POP3

IMAP & POP3 clients are available, but are becoming less supported. Their usage has been deprecated in some
popular enterprise mail platforms. Use with caution.

```php
use Pop\Mail\Client\Imap;

$imap = new Client\Imap('imap.gmail.com', 993);
$imap->setUsername('me@domain.com')
     ->setPassword('password');

$imap->setFolder('INBOX');
$imap->open('/ssl');

// Sorted by date, reverse order (newest first)
$ids     = $imap->getMessageIdsBy(SORTDATE, true);
$headers = $imap->getMessageHeadersById($ids[0]);
$parts   = $imap->getMessageParts($ids[0]);

// Assuming the first part is an image attachment, display image
header('Content-Type: image/jpeg');
header('Content-Length: ' . strlen($parts[0]->content));
echo $parts[0]->content;
```

[Top](#pop-mail)

Mail Queues
-----------

You can create a mail queue to manage and send messages to multiple recipients at the same time.
The benefit is that the body of the messages can contain placeholders that can be swapped for
individual user data for customization and a better user experience.

```php
use Pop\Mail\Message;
use Pop\Mail\Queue;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$queue = new Queue();
$queue->addRecipient([
    'email'   => 'me@domain.com',
    'name'    => 'My Name',
    'company' => 'My Company',
    'url'     => 'http://www.domain1.com/'
]);
$queue->addRecipient([
    'email'   => 'another@domain.com',
    'name'    => 'Another Name',
    'company' => 'Another Company',
    'url'     => 'http://www.domain2.com/'
]);

$messageBody = <<<TEXT
How are you doing? Your [{company}] is great!
I checked it out at [{url}]
TEXT;

$message = new Message('Hello [{name}]!');
$message->setFrom('noreply@domain.com');
$message->setBody($messageBody);

$queue->addMessage($message);

$mailer = new Mailer(new Sendmail());
$mailer->sendFromQueue($queue);
```

[Top](#pop-mail)

Saving Mail
-----------

By saving mail messages to a storage location within your application, you can call them up at a later
date and time to send them from that location.

```php
use Pop\Mail\Message;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$message1 = new Mail\Message('Hello World');
$message1->setTo('user1@domain.com');
$message1->setFrom('me@domain.com');
$message1->addText('Hello World! This is a test!');
$message1->addHtml('<html><body><h1>Hello World!</h1><p>This is a test!</p></body></html>');
$message1->save(__DIR__ . '/mail-queue/message1.msg'); 

$message2 = new Mail\Message('Hello World');
$message2->setTo('user2@domain.com');
$message2->setFrom('me@domain.com');
$message2->addText('Hello World! This is a test!');
$message2->addHtml('<html><body><h1>Hello World!</h1><p>This is a test!</p></body></html>');
$message2->save(__DIR__ . '/mail-queue/message2.msg'); 

$mailer = new Mailer(new Sendmail());
$mailer->sendFromDir(__DIR__ . '/mail-queue');
```

[Top](#pop-mail)
