pop-mail
========

[![Build Status](https://github.com/popphp/pop-mail/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-mail/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-mail)](http://cc.popphp.org/pop-mail/)

OVERVIEW
--------
`pop-mail` is the main mail component for the Pop PHP Framework. It provides a robust set of features to manage the
many aspects of sending and receiving mail over the internet. It provides functionality for the following:

- **Mail Clients**
  - Manage mailboxes, their messages, data, attachments and statuses from a server-to-server application connection*
    - **Google Client**: utilizes the Gmail API
    - **Office 365 Client**: utilizes the Office 365 Mail API
    - **IMAP/POP3 Client**: utilizes standard a IMAP or POP3 connection
      *(for security purposes, IMAP/POP3 are not as widely supported anymore and their usage has been deprecated in some popular enterprise mail platforms)*
- **Mail Transports**
  - Manage creating and sending email messages with multiple mime types and attachments from a server-to-server application connection*
  - Send emails to a queue of recipients, with individual message customization
  - Save emails to be sent later
  - The available mail transports are interchangeable:
    - **Mailgun Transport**: utilizes the Mailgun API
    - **Sendgrid Transport**: utilizes the Sendgrid API
    - **Google Transport**: utilizes the Gmail API
    - **Office 365 Transport**: utilizes the Office 365 Mail API
    - **AWS SES Transport**: utilizes the AWS SDK
    - **SMTP Transport**: utilizes a standard SMTP connection
    - **Sendmail Transport**: utilizes a basic Sendmail connection

\* **NOTE:** - The main use-case for this component is that of a server-to-server application connection. This means
that the component would be used with an application that has been granted the appropriate access and permissions to
act on behalf of the user or users whose emails are being managed by the application. This component is not geared
towards an individual user mail application use-case. Please refer to the online documentation, guidelines and polices
for whichever mail platforms to which you are attempting to connect your application using this component. Please take
care in granting access and assigning permissions to your application instance. Always follow the recommended security
policies and guidelines of your chosen mail platform.  

`pop-mail` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-mail` using Composer.

    composer require popphp/pop-mail

Or, require it in your composer.json file

    "require": {
        "popphp/pop-mail" : "^4.0.0"
    }

BASIC USAGE
-----------

### Sending a basic email via sendmail

```php
use Pop\Mail;

$transport = new Mail\Transport\Sendmail()

$mailer = new Mail\Mailer($transport);

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->attachFile(__DIR__ . '/image.jpg');
$message->setBody('Hello World! This is a test!');

$mailer->send($message);
```

### Sending a basic email via SMTP (MS Exchange example)

```php
use Pop\Mail;

$transport = new Mail\Transport\Smtp('mail.msdomain.com', 587);
$transport->setUsername('me')
    ->setPassword('password');

$mailer = new Mail\Mailer($transport);

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->attachFile(__DIR__ . '/image.jpg');
$message->setBody('Hello World! This is a test!');

$mailer->send($message);
```

### Sending a basic email via SMTP (Gmail example)

```php
use Pop\Mail;

$transport = new Mail\Transport\Smtp('smtp.gmail.com', 587, 'tls');
$transport->setUsername('me@mydomain.com')
    ->setPassword('password');

$mailer = new Mail\Mailer($transport);

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->attachFile(__DIR__ . '/image.jpg');
$message->setBody('Hello World! This is a test!');

$mailer->send($message);
```

### Sending a basic email via a mail API (Mailgun example)

```php
use Pop\Mail;

$transport = new Mail\Transport\Mailgun('https://api.mailgun.net/v3/YOUR_DOMAIN_HERE/messages', 'YOUR_API_KEY');
$mailer    = new Mail\Mailer($transport);

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');
$message->attachFile(__DIR__ . '/image.jpg');
$message->setBody('Hello World! This is a test!');

$mailer->send($message);
```

### Attaching a file from data

```php
use Pop\Mail;

$mailer = new Mail\Mailer(new Mail\Transport\Sendmail());

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');

$fileData = file_get_contents($fileData);

$message->attachFileFromStream($fileData, 'image.jpg');
$message->setBody('Hello World! This is a test!');

$mailer->send($message);
```

### Sending an HTML and text email

```php
use Pop\Mail;
$mailer = new Mail\Mailer(new Mail\Transport\Sendmail());

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');

$message->addText('Hello World! This is a test!');
$message->addHtml('<html><body><h1>Hello World!</h1><p>This is a test!</p></body></html>');

$mailer->send($message);
```

### Sending emails to a queue

```php
use Pop\Mail;

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

$message = new Mail\Message('Hello [{name}]!');
$message->setFrom('noreply@domain.com');
$message->setBody(
<<<TEXT
How are you doing? Your [{company}] is great!
I checked it out at [{url}]
TEXT
);

$queue->addMessage($message);

$mailer = new Mail\Mailer(new Mail\Transport\Sendmail());
$mailer->sendFromQueue($queue);
```

### Saving emails to send later

```php
use Pop\Mail;

$message = new Mail\Message('Hello World');
$message->setTo('you@domain.com');
$message->setFrom('me@domain.com');

$message->addText('Hello World! This is a test!');
$message->addHtml('<html><body><h1>Hello World!</h1><p>This is a test!</p></body></html>');

$message->save(__DIR__ . '/mailqueue/test.msg'); 

$mailer = new Mail\Mailer(new Mail\Transport\Sendmail());
$mailer->sendFromDir(__DIR__ . '/mailqueue');
```

### Retrieving emails from a client

```php
use Pop\Mail\Client;

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
