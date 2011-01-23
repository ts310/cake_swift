<?php

App::import('Vendor', 'Swift', array(
    'file'   => 'swift' . DS . 'lib' . DS . 'swift_required.php',
    'plugin' => 'swift'
));

/**
 * Simple swift mailer wrapper component
 *
 * @author Tsuyoshi Saito
 */
class SwiftComponent extends Object {
    var $controller = null;

    function initialize(&$controller, $settings = array()) {
        $this->controller = $controller;
    }

    function startup(&$controller) {
        $this->controller = $controller;
    }

    /**
     * Sending email message
     *
     * $this->Swift->send(array(
     *     'subject' => 'Your mail subject line',
     *     'from' => array('foo@foo.com' => 'Foo Bar'),
     *     'to' => array('foo@bar.com' => 'Foo Bar'),
     *     'textBody' => 'Text email body text',
     *     'htmlBody' => '<p>HTML email body text</p>'
     * ));
     *
     * @param array $options[optional]
     * @return integer
     */
    public function send($options = array()) {
        extract($options);
        // Swiftmailer
        $mailer = $this->getMailer();
        // Message object
        $message = Swift_Message::newInstance($subject)
            ->setFrom($from)
            ->setTo($to)
            ;
        if (!empty($cc)) $message->setCc($cc);
        if (!empty($bcc)) $message->setBcc($bcc);
        if (!empty($replyTo)) $message->setReplyTo($replyTo);
        // Text message
        if (!empty($textBody)) {
            $textBody = str_replace(array("\r\n", "\r"), "\n", $textBody);
            $message->setBody($textBody);
        }
        // HTML message
        if (!empty($htmlBody)) $message->addPart($htmlBody, "text/html");
        // Sending message
        return $mailer->send($message);
    }

    /**
     * Render email message
     * @return string
     * @param string $viewFile
     * @param array $viewVars[optional]
     * @param array $options[optional]
     */
    public function render($viewFile, $viewVars = array(), $options = array()) {
        // Creating new controller to render email view
        App::import('Core', array('Controller', 'Router'));
        $controller = new Controller;
        if (!$this->controller) $this->controller = $controller;
        // Setting controller property
        $helpers = array('Html');
        $property = array(
            'helpers'  => isset($this->controller->helpers) ? am($helpers, $this->controller->helpers) : $helpers,
            'view'     => isset($this->controller->view) ? $this->controller->view : 'View',
            'theme'    => isset($this->controller->theme) ? $this->controller->theme : null,
            'plugin'   => isset($this->controller->plugin) ? $this->controller->plugin : null,
            'layout'   => null, // Layout is null as default
            'viewPath' => isset($this->controller->viewPath) ? $this->controller->viewPath : 'email'
        );
        $property = am($property, $options);
        extract($property);
        $controller->view = $view;
        $controller->helpers = $helpers;
        $controller->theme = $theme;
        $controller->plugin = $plugin;
        $controller->layout = $layout;
        $controller->viewPath = $viewPath;
        $controller->set($viewVars);
        $controller->render($viewFile);
        return $controller->output;
    }

    /**
     * Set mailer backend
     * @return object SwiftMailer
     */
    private function getMailer() {
        // Load Swift mailer library
        $config = !Configure::read('ServerMailer.backend') ?
            array('backend' => 'mail')
            : Configure::read('ServerMailer');

        switch ($config['backend']) {
            case "smtp":
                $host = !empty($config['options']['host']) ? $config['options']['host'] : 'localhost';
                $port = !empty($config['options']['port']) ? $config['options']['port'] : 25;
                $transport = Swift_SmtpTransport::newInstance($host, $port);
                if(!empty($config['options']['username']))
                    $transport->setUsername($config['options']['username']);
                if(!empty($config['options']['password']))
                    $transport->setPassword($config['options']['password']);
                break;
            case "sendmail":
                $path = !empty($config['options']['path']) ?
                    $config['options']['path'] : '/usr/sbin/exim -bs';
                $transport = Swift_SendmailTransport::newInstance($path);
                break;
            case "mail":
                $transport = Swift_MailTransport::newInstance();
                break;
        }
        //Create the Mailer instance using created Transport
        return Swift_Mailer::newInstance($transport);
    }
}
