<?php
declare(strict_types=1);

namespace Ipws\EmailLabs\Transport;

use Exception;
use GuzzleHttp\ClientInterface;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Support\Facades\Http;
use Ipws\EmailLabs\ApiClient;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;

class EmailLabsTransport extends AbstractTransport
{
    //  protected string $app;
    //  protected string $secret;
    private const ENDPOINT = 'https://api.emaillabs.net.pl/api/new_sendmail';
    protected ApiClient $client;
    /**
     * The EmailLabs API Secret Key
     *
     * @var string
     **/
    protected string $secret;

    /**
     * The EmailLabs smtp account name
     *
     * @var string
     **/
    protected string $smtpAccount;

    /**
     * The EmailLabs API App Key
     *
     * @var string
     **/
    protected string $app;
    protected array $options;

    public function __construct(array $config = [])
    {

        //$config = config('services.emaillabs');
        $this->client = new ApiClient();
        $this->secret = $config['secret'];
        $this->app = $config['app'];
        $this->smtpAccount = $config['smtp'];// $config['smtp'];
        parent::__construct();
    }


    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'emaillabs';
    }

    /**
     * Get the Api client for EmailLabsTransport instance.
     *
     * @return ApiClient
     */
    public function client(): ApiClient
    {
        return $this->client;
    }

    /**
     * Get the transmission options being used by the transport.
     *
     * @return array
     */
    public
    function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the transmission options being used by the transport.
     *
     * @param array $options
     * @return array
     */
    public
    function setOptions(array $options): array
    {
        return $this->options = $options;
    }


    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {

        $options = [];
        $payload = $this->prepareData($message);

        if ($message->getOriginalMessage() instanceof Message) {
            foreach ($message->getOriginalMessage()->getHeaders()->all() as $header) {
                if ($header instanceof MetadataHeader) {
                    $options['Tags'][] = ['Name' => $header->getKey(), 'Value' => $header->getValue()];
                }
            }
        }

        try {
            $token = base64_encode($this->app . ':' . $this->secret);
            $response = Http::withOptions([
                'debug' => config('app.debug', false),
                'auth' => [
                    $this->app,
                    $this->secret, 'basic'
                ]
            ])->post(self::ENDPOINT, ['body' => $payload]);

            //$messageId = $response->get('MessageId');
            //$message->getOriginalMessage()->getHeaders()->addHeader('X-Message-ID', $messageId);
        } catch (Exception $e) {
            throw $e;
        }


    }

    /**
     * Prepare message data array from Swift message
     *
     * @return array
     * @author
     **/
    protected function prepareData(SentMessage $message)
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $data = [
            'smtp_account' => $this->smtpAccount,
            'to' => $this->getAddresses($email->getTo()),
            'multi_cc' => '1',
            'multi_bcc' => '1',
            //  'cc' => $this->getAddresses($email->getCc()),
            // 'cc_name' => $this->getFirstNameFromAddresses($email->getCc()),
            'new_structure' => '1',
            // 'bcc' => $this->getFirstNameFromAddresses($email->getBcc()),
            //'bcc_name' => $this->getFirstNameFromAddresses($email->getBcc()),
            'from' => 'kontakt@ipws.pl',
            //  'from_email'=>'kontakt@ipws.pl',
            //   'from_name' => $this->getFirstNameFromAddresses($email->getFrom()),
            //   'reply_to' => $this->getFirstNameFromAddresses($email->getReplyTo()),
            'html' => $email->getBody(),
            'text' => $email->getTextBody(),
            'subject' => substr($email->getSubject(), 0, 128)
        ];
        return $data;
    }

    /**
     * Get all the addresses this message should be sent to.
     *
     * @param array|null $addresses
     * @return array
     * @author
     **/
    protected function getAddresses($addresses): array
    {

        $to = [];

        if ($addresses) {

            $to = array_merge($to, collect($addresses)->map(fn($el) => $el->getAddress())->toArray());
        }

        return $to;
    }

    /**
     * Get API key
     *
     * @return string
     * @author Sebastian
     **/
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Convert response data array to string
     *
     * @return string
     * @author
     **/
    protected function formatResponseData($response): string
    {
        $result = '';
        foreach ($response['data'][0] as $key => $value) {
            $result .= $key . ':' . $value . ';';
        }

        return $result;
    }

    /**
     * Recive if exists first recipient name from addresses array
     *
     * @return string
     * @author
     **/
    protected function getFirstNameFromAddresses($addresses): string
    {
        if ($addresses) {

            return substr($addresses[0]->getName(), 0, 128);
        }

        return '';
    }
}
