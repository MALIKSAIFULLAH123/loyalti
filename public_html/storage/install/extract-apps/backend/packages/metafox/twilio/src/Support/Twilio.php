<?php

namespace MetaFox\Twilio\Support;

use MetaFox\Sms\Support\AbstractService;
use MetaFox\Sms\Support\Message;
use Twilio\Rest\Client;

class Twilio extends AbstractService
{
    /**
     * @var Client
     */
    private Client $client;

    /**
     * Get the value of client.
     *
     * @return Client
     */
    public function getClient(): Client
    {
        if (empty($this->client)) {
            $accountSid = $this->getConfig('sid');
            $authToken  = $this->getConfig('auth_token');

            $this->client = new Client($accountSid, $authToken);
        }

        return $this->client;
    }

    /**
     * Set the value of client.
     *
     * @param Client $client
     *
     * @return self
     */
    public function setClient(Client $client)
    {
        $this->client = $client;

        return $this;
    }

    public function send(Message $message)
    {
        $fromNumber = $this->getConfig('number');
        $recipients = $message->getRecipients();

        foreach ($recipients as $recipient) {
            $this->getClient()->messages->create($recipient, [
                'from' => $fromNumber,
                'body' => $message,
            ]);
        }
    }

    public function validatePhoneNumber(string $phoneNumber): bool
    {
        return (bool) $this->getClient()->lookups->v2
            ->phoneNumbers($phoneNumber)->fetch()->valid;
    }
}
