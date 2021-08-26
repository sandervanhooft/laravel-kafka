<?php

namespace Junges\Kafka\Producers;

use Junges\Kafka\Config;
use Junges\Kafka\Contracts\CanProduceMessages;
use Junges\Kafka\Message;

class ProducerBuilder implements CanProduceMessages
{
    private array $options = [];
    private Message $message;

    public function __construct(
        private string $broker,
        private string $topic,
    ) {
        $this->message = new Message();
    }

    /**
     * Return a new Junges\Kafka\ProducerBuilder instance
     * @param string $broker
     * @param string $topic
     * @return static
     */
    public static function create(string $broker, string $topic): self
    {
        return new ProducerBuilder($broker, $topic);
    }

    public function withConfigOption(string $name, string $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function withConfigOptions(array $options): self
    {
        foreach ($options as $name => $value) {
            $this->withConfigOption($name, $value);
        }

        return $this;
    }

    /**
     * Set the message headers.
     * @param array $headers
     * @return $this
     */
    public function withHeaders(array $headers): self
    {
        $this->message->withHeaders($headers);

        return $this;
    }

    /**
     * Set the message key.
     * @param string $key
     * @return $this
     */
    public function withKey(string $key): self
    {
        $this->message->withKey($key);

        return $this;
    }

    /**
     * Set a message array key.
     * @param string $key
     * @param mixed $message
     * @return ProducerBuilder
     */
    public function withMessageKey(string $key, mixed $message): self
    {
        $this->message->withMessageKey($key, $message);

        return $this;
    }

    public function withMessage(Message $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function withDebugEnabled(bool $enabled = true): self
    {
        if ($enabled) {
            $this->withConfigOptions([
                'log_level' => LOG_DEBUG,
                'debug' => 'all',
            ]);
        } else {
            unset($this->options['log_level']);
            unset($this->options['debug']);
        }

        return $this;
    }

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function getMessage(): Message
    {
        return $this->message;
    }

    public function send(): bool
    {
        $producer = $this->build();

        return $producer->produce($this->message);
    }

    private function build(): Producer
    {
        $conf = new Config(
            broker: $this->broker,
            topics: [$this->getTopic()],
            customOptions: $this->options
        );

        return app(Producer::class, [
            'config' => $conf,
            'topic' => $this->topic,
        ]);
    }
}