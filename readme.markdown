# Simple Swift mailer component

Download swift mailer from 
http://swiftmailer.org/

Extract library to /plugins/swift/vendors/swift

    Configure::write('ServerMailer.backend', 'smtp');
    Configure::write('ServerMailer.backend.options', array('host' => 'localhost', 'port' => 25));

    $this->Swift->send(array(
        'subject'  => 'Your subject line',
        'from'     => array('foo@foo.com' => 'Foo Bar'),
        'to'       => array('foo@bar.com' => 'Foo Bar'),
        'textBody' => 'Text email',
        'htmlBody' => '<p>HTML email</p>'
    ));
