<?php

namespace App\Support;

class DnsResolver
{
    /**
     * Resolve both IPv4 and IPv6 addresses for the given hostname.
     *
     * @return list<string>
     */
    public function resolve(string $host): array
    {
        // dns_get_record() emits a warning (which Laravel turns into an exception)
        // when the DNS server fails, treat that the same as an unresolvable host.
        $records = rescue(fn () => @dns_get_record($host, DNS_A | DNS_AAAA), [], report: false);

        if (! is_array($records)) {
            return [];
        }

        $ips = [];

        foreach ($records as $record) {
            $ip = $record['ip'] ?? $record['ipv6'] ?? null;

            if (is_string($ip)) {
                $ips[] = $ip;
            }
        }

        return $ips;
    }
}
