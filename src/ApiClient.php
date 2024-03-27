<?php
declare(strict_types=1);

namespace Ipws\EmailLabs;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Http\Message\Authentication\Header;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
    private const ENDPOINT = 'https://api.emaillabs.net.pl/api/new_sendmail';
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
    protected $client;
    protected array $options;

    public function __construct($config)
    {
     //   $config = config('services.emaillabs');
        $this->secret = $config['secret'];
        $this->app = $config['app'];
        $this->smtpAccount = $config['smtp'];
      //  $this->client = $this->buildClient();
    }


    public function send(array $payload=[])
    {
        $response = Http::withBasicAuth($this->app, $this->secret)
            ->asForm()
            ->post(self::ENDPOINT, $payload);

        return $response;
    }

}
