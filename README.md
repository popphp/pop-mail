pop-mail
========

[![Build Status](https://travis-ci.org/popphp/pop-mail.svg?branch=master)](https://travis-ci.org/popphp/pop-mail)
[![Coverage Status](http://www.popphp.org/cc/coverage.php?comp=pop-mail)](http://www.popphp.org/cc/pop-mail/)

OVERVIEW
--------
`pop-mail` is a component for managing and sending email messages. It has a full feature set that supports:

* Send to multiple emails
* Send as group
* Manage headers
* Attach files
* Send multiple mime-types (i.e., text, HTML, etc.)
* Save emails to be sent later

`pop-mail` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-mail` using Composer.

    composer require popphp/pop-mail

BASIC USAGE
-----------

### Sending a basic email

```php
use Pop\Mail\Mail;

$mail = new Mail('Test Email Subject');

$mail->to('test@test.com');
$mail->cc('cc@test.com');
$mail->from('somebody@test.com');

$mail->setText('Hello World! This is a test email.');

$mail->send();
```

```
To: test@test.com
Subject: Test Email Subject
Cc: cc@test.com
From: somebody@test.com
Reply-To: somebody@test.com
Content-Type: text/plain; charset=utf-8

Hello World! This is a test email.
```

### Attaching a file

```php
use Pop\Mail\Mail;

$mail = new Mail('Attaching a File');

$mail->to('test@test.com');
$mail->from('somebody@test.com');

$mail->setText('Check out this file.');
$mail->attachFile('lorem.docx');

$mail->send();
```

```
To: test@test.com
Subject: Attaching a File
From: somebody@test.com
Reply-To: somebody@test.com
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="7dbf357ee8df3d00a00cda688da71a8523f8123c"
This is a multi-part message in MIME format.



--7dbf357ee8df3d00a00cda688da71a8523f8123c
Content-Type: file; name="lorem.docx"
Content-Transfer-Encoding: base64
Content-Description: lorem.docx
Content-Disposition: attachment; filename="lorem.docx"

UEsDBBQACAgIAKmB9UYAAAAAAAAAAAAAAAALAAAAX3JlbHMvLnJlbHOtkk1LA0EMhu/9FUPu3Wwr
iMjO9iJCbyL1B4SZ7O7Qzgczaa3/3kEKulCKoMe8efPwHNJtzv6gTpyLi0HDqmlBcTDRujBqeNs9
[ ... Big long block of base 64 encoded data ... ]
L2NvcmUueG1sUEsBAhQAFAAICAgAqYH1RhkaEIMtAQAAXgQAABMAAAAAAAAAAAAAAAAAcRAAAFtD
b250ZW50X1R5cGVzXS54bWxQSwUGAAAAAAkACQA8AgAA3xEAAAAA


--7dbf357ee8df3d00a00cda688da71a8523f8123c
Content-type: text/plain; charset=utf-8

Check out this file.

--7dbf357ee8df3d00a00cda688da71a8523f8123c--

```

### Sending an HTML and text-based email

```php
$mail = new Mail('Sending an HTML Email');

$mail->to('test@test.com');
$mail->from('somebody@test.com');

$html = <<<HTML
<html>
<head>
<title>Hello World!</title>
</head>
<body>
<h1>Hello World!</h1>
<p>This is a cool HTML email, huh?</p>
</body>
</html>
HTML;

$mail->setHtml($html);
$mail->setText(
    'This is the text message in case your email client cannot display HTML.'
);

$mail->send();
```

```
To: test@test.com
Subject: Sending an HTML Email
From: somebody@test.com
Reply-To: somebody@test.com
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary="d08ae99249fe6d0a03a8436ce3bea4ceffd208cb"
This is a multi-part message in MIME format.


--d08ae99249fe6d0a03a8436ce3bea4ceffd208cb
Content-type: text/plain; charset=utf-8

This is the text message in case your email client cannot display HTML.

--d08ae99249fe6d0a03a8436ce3bea4ceffd208cb
Content-type: text/html; charset=utf-8

<html>
<head>
<title>Hello World!</title>
</head>
<body>
<h1>Hello World!</h1>
<p>This is a cool HTML email, huh?</p>
</body>
</html>

--d08ae99249fe6d0a03a8436ce3bea4ceffd208cb--

```

### Saving an email to send later

```php
use Pop\Mail\Mail;

$mail = new Mail('Test Email Subject');

$mail->to('test@test.com');
$mail->cc('cc@test.com');
$mail->from('somebody@test.com');

$mail->setText('Hello World! This is a test email.');
$mail->saveTo(__DIR__ . '/email-queue');
```

That will write the email or emails to a file in the folder.
Then, when you're ready to send them, you can simply do this:

```php
use Pop\Mail\Mail;

$mail = new Mail();
$mail->sendFrom(__DIR__ . '/email-queue', true);
```

The `true` parameter is the flag to delete the email from the folder once it's sent.
 