<?php

/* this is auto generated file */
return [
    'array_transport_guide'    => 'Do not send out email, it\'s helpful for development.',
    'failover_transport_guide' => 'Sometimes, an external email service you have configured to send your application\'s email may be down. Thus, it can be useful to define one or more email configurations that will be used in case your primary mail service is down.',
    'log_transport_guide'      => 'Instead of sending emails out, the log mail driver will write all email messages to log files for inspection. Typically, this email option would only be used during local development.',
    'mailgun_transport_guide'  => 'The leading email delivery service for businesses around the world powerful APIs that enable you to send, receive, and track email effortlessly.',
    'postmark_transport_guide' => 'A transactional email service you can count on. Postmark delivers your transactional emails on time, every time. https://postmarkapp.com/transactional-email',
    'sendmail_transport_guide' => 'PHP sendmail is the built in PHP function that is used to send emails from PHP scripts.',
    'ses_transport_guide'      => 'Amazon Simple Email Service (SES) is a cost-effective, flexible, and scalable email service that enables developers to send mail from within any application. You can configure Amazon SES quickly to support several email use cases, including transactional, marketing, or mass email communications.',
    'smtp_transport_guide'     => 'Simple Mail Transfer Protocol (STMP) is used to send and receive email. It is sometimes paired with IMAP or POP3 (for example, by a user-level application), which handles the retrieval of messages, while SMTP primarily sends messages to a server for forwarding.',
];
