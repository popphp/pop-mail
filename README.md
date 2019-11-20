pop-mail
========

[![Build Status](https://travis-ci.org/popphp/pop-mail.svg?branch=master)](https://travis-ci.org/popphp/pop-mail)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-mail)](http://cc.popphp.org/pop-mail/)

OVERVIEW
--------
`pop-mail` is a component for managing and sending email messages. It has a full feature set that supports:

* Send to email via sendmail, SMTP or any custom-written mail transport adapters
* Send emails to a queue of recipients, with individual message customization
* Save emails to be sent later
* Retrieve and manage emails from email mailboxes.

`pop-mail` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-mail` using Composer.

    composer require popphp/pop-mail

### A Note about SMTP
The SMTP transport component within `pop-mail` is forked from and built on top of the SMTP features and
functionality of the [Swift Mailer Library](https://github.com/swiftmailer/swiftmailer) and the great
work the Swift Mailer team has accomplished over the past years.

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
