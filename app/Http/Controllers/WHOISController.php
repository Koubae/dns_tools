<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Constants\WhoIs as WHOIS;


class WHOISController extends Controller
{

    private array $payload;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->payload = [
            'error' => null,
            'data' => null,
            'time_requested' => time(),
        ];
    }

    /*
     |-----------------------------------------------------
     | Endpoints
     |-----------------------------------------------------
     |
     |
    */

    /**
     * @TODO: endpoint + docs
     * @return JsonResponse
     */
    public function info(): JsonResponse
    {
        return response()->json(['WHOIS Servers' => WHOIS::WHOIS_SERVERS]);
    }

    public function get(string $domain, string $tld): JsonResponse
    {
        $this->whoisRequest($domain, $tld);
        return response()->json($this->payload);
    }

    /*
     |----------------------------------------------------
     | Private Methods
     |----------------------------------------------------
     |
    */

    private function whoisRequest(string $domain, string $tld): bool
    {
        $rootDomain = $domain . '.' .  $tld;
        // Validate the domain input
        $isValid = $this->validateDomain($rootDomain);
        if (!$isValid) {
            return false;
        }
        // connect via tcp
        $connection = $this->createConnection($tld, $rootDomain);
        if (!$connection) return false;
        [$whoisTCP, $whoIsServer] = $connection;
        // send request and prepare response
        $send = $this->sendTCP($whoisTCP, $rootDomain, $whoIsServer);
        if (!$send) return false;
        return true;

    }

    /**
     * Sends Actual request to WHOIS server via TCP connection
     * @param resource $whoisTCP
     * @param string $rootDomain
     * @param string $whoIsServer
     * @return array containing response to send to client
     */
    private function sendTCP($whoisTCP, string $rootDomain, string $whoIsServer): bool
    {
        fputs($whoisTCP, $rootDomain . "\r\n");
        $response = "";
        while(!feof($whoisTCP)) {
            $response .= fgets($whoisTCP);
        }
        fclose($whoisTCP);
        if (empty($response)) {
            $this->payload['error'] = "No data returned from WHOIS server $whoIsServer for domain $rootDomain";
            return false;
        }
        $this->payload['success'] = $response;
        return true;
    }

    /**
     * Validate a domain entry: yahoo.com.com : not good | yahoo._com : not good and so on
     * @docs https://www.php.net/filter.filters.validate
     * @param string $rootDomain
     * @return bool
     */
    private function validateDomain(string $rootDomain): bool
    {
        $valid = filter_var($rootDomain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        if (!$valid) {
            $this->payload['error'] = "The domain name $rootDomain is not a valid domain for a WHOIS lookup";
            return false;
        }
        return true;
    }

    /**
     * Creates TCP connection and finds whois server mapping
     * @param string $tld
     * @param string $rootDomain
     * @return array|false
     */
    private function createConnection(string $tld, string $rootDomain): array|false
    {
        if (!array_key_exists($tld, WHOIS::WHOIS_SERVERS)) {
            $this->payload['error'] = "Sorry, WHOIS server for $rootDomain domain not found!";
            return false;
        }
        // Get WHOIS server and create TCP connection
        $whoIsServer = WHOIS::WHOIS_SERVERS[$tld];
        $whoisTCP = WHOIS::whoIsConnect($whoIsServer);
        if (!$whoisTCP) {
            $this->payload['error'] = "Error while establish to WHOIS server $whoIsServer for domain $rootDomain";
            return false;
        }
        return [$whoisTCP, $whoIsServer];
    }


}
