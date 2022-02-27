<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Hostinger\DigClient as Dig;
use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;


class DigController extends Controller
{
    /**
     * @var array <string, int> DNS_TYPE
     */
    public const DNS_TYPE = [
        'a' => DNS_A,
        'cname' => DNS_CNAME,
        'hinfo' => DNS_HINFO,
        'caa' => DNS_CAA,
        'mx' => DNS_MX,
        'ns' => DNS_NS,
        'ptr' => DNS_PTR,
        'soa' => DNS_SOA,
        'txt' => DNS_TXT,
        'aaaa' => DNS_AAAA,
        'srv' => DNS_SRV,
        'naptr' => DNS_NAPTR,
        'a6' => DNS_A6,
        'all' => DNS_ALL,
        'any' => DNS_ANY

    ];
    private const LOG_LOGGER_NAME = 'DigService';
    private const LOG_PATH = "logs/dig";
    private const LOG_NAME = "dig";
    private const DIG_DOCS = <<<DOCS
<h2 role="heading" aria-level="2">DNS Records</h2>
<p>The information returned to your <code>dig</code> requests is pulled from different types of records held on the DNS server. Unless we ask for something different, <code>dig</code> queries the A (address) record. The following are the types of records commonly used with <code>dig</code>:</p>
<ul>
<li><strong>A Record:</strong>&nbsp;Links the domain to an IP version 4 address.</li>
<li><strong>MX Record:</strong>&nbsp;Mail exchange records direct emails sent to domains to the correct mail server.</li>
<li><strong>NS Record:</strong> Name server records delegate a domain (or subdomain) to a set of DNS servers.</li>
<li><strong>TXT Record:</strong> Text records store text-based information regarding the domain. Typically, they might be used to suppress spoofed or forged email.</li>
<li><strong>SOA Record:</strong> Start of authority records can hold a lot of information about the domain. Here, you can find the primary name server, the responsible party, a timestamp for changes, the frequency of zone refreshes, and a series of time limits for retries and abandons.</li>
<li><strong>TTL:</strong> Time to live is a setting for each&nbsp;DNS record&nbsp;that specifies how long a DNS precursor server is allowed to cache each&nbsp;DNS&nbsp;query. When that time expires, the data must be refreshed for subsequent requests.</li>
<li><strong>ANY:</strong> This tells <code>dig</code> to return every type of DNS record it can.</li>
</ul>
DOCS;


    private array $payload;
    private Dig $client;
    private MonoLogger $logger;

    public function __construct() {
        $this->payload = [
            'error' => null,
            'data' => null,
            'time_requested' => time(),
        ];
        $this->client = new Dig();
        $this->logger = new MonoLogger(self::LOG_LOGGER_NAME);
        $this->logger->pushHandler(new StreamHandler(storage_path($this->logBuildName()), MonoLogger::DEBUG));
        $this->client->setLogger($this->logger);

    }

    /*
     |-----------------------------------------------------
     | Endpoints
     |-----------------------------------------------------
     |
     |
    */

    /**
     * @todo: endpoti + docs
     * @return Response|JsonResponse
     */
    public function info(Request $request): Response|JsonResponse
    {
        $type = $request->get('type');
        if ($type && $type === 'json') {
            $this->payload['success'] = strip_tags(self::DIG_DOCS);
            return response()->json(['DIG Server' => true, 'docs' => $this->payload ]);
        }
        return response(self::DIG_DOCS);

    }

    public function get(Request $request): JsonResponse
    {
        $domain = $request->get('domain');
        $dig = $request->get('dig');
        if (empty($dig)) $dig = 'any';

        $valid = $this->validateDigCommand($domain, $dig);
        if (!$valid) {
            return response()->json($this->payload);
        }

        $response = $this->client->getRecord($domain, self::DNS_TYPE[$dig]);
        $this->payload['data'] = $response;
        return response()->json($this->payload);
    }

    /*
     |----------------------------------------------------
     | Private Methods
     |----------------------------------------------------
     |
    */

    /**
     * @param string $domain
     * @param string $dig
     * @return bool
     */
    private function validateDigCommand(string $domain, string $dig): bool
    {
        if (empty($domain)) {
            $this->payload['error'] = "Domain required for the dig service!"
                . implode(',', array_keys(self::DNS_TYPE));

            return false;
        }
        if (!array_key_exists($dig, self::DNS_TYPE)) {
            $this->payload['error'] = "Dig command $dig not allowed! Available commands are : "
                . implode(',', array_keys(self::DNS_TYPE));

            return false;
        }
        return true;
    }

    private function logBuildName(): string
    {
        return self::LOG_PATH . '/' . self::LOG_NAME . "-" . date("d-m-Y") . '.log';
    }

}
