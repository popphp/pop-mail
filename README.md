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

$message = new Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');
```

Create a mail message with both text and HTML parts:

```php
use Pop\Mail\Message;

$message = new Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->addText('Hello World! This is a text body!');
$message->addHtml('<html><body><h1>Hello World!</h1><p>This is an HTML body!</p></body></html>');
```

Create a mail message with a file attachment:

```php
use Pop\Mail\Message;

$message = new Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');
$message->attachFile(__DIR__ . '/image.jpg');
```

Create a mail message with a file attachment from a stream of file contents:

```php
use Pop\Mail\Message;

$message = new Message('Hello World');
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

$transport = new Sendmail()
$mailer    = new Mailer($transport);
$message   = new Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->setBody('Hello World! This is a text body!');

$mailer->send($message);
```

Transports
----------

### Mailgun

### Sendgrid

### Office 365

### AWS SES

### Google

### SMTP

### Sendmail

Clients
-------

### Office 365 Client

### Google Client

### IMAP/POP3

Mail Queues
-----------

Saving Mail
-----------

