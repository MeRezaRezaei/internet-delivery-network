<?php

namespace Tests\Feature;

use App\Facades\Technitium;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TechnitiumApiTest extends TestCase
{
    public function test_set_blocklist_success()
    {
        Http::fake([
            '*/api/settings/set*' => Http::response(['status' => 'ok'], 200),
        ]);

        $result = Technitium::setBlocklist(true);

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'blocklist=true');
        });
    }

    public function test_update_record_success()
    {
        Http::fake([
            '*/api/zones/records/add*' => Http::response(['status' => 'ok'], 200),
        ]);

        $result = Technitium::updateRecord('test.idn', 'A', '100.100.1.1');

        $this->assertTrue($result);
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'domain=test.idn') &&
                   str_contains($request->url(), 'type=A') &&
                   str_contains($request->url(), 'value=100.100.1.1');
        });
    }
}
