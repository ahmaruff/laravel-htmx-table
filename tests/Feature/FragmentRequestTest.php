<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Feature;

use Ahmaruff\HtmxTable\HtmxTable;
use Ahmaruff\HtmxTable\Tests\TestCase;
use Illuminate\Http\Request;

final class FragmentRequestTest extends TestCase
{
    public function test_plain_htmx_requests_are_treated_as_fragment_requests(): void
    {
        $request = Request::create('/items', 'GET', [], [], [], ['HTTP_HX_REQUEST' => 'true']);

        $this->assertTrue(HtmxTable::isFragmentRequest($request));
    }

    public function test_history_restore_requests_are_not_treated_as_fragment_requests(): void
    {
        $request = Request::create('/items', 'GET', [], [], [], [
            'HTTP_HX_REQUEST' => 'true',
            'HTTP_HX_HISTORY_RESTORE_REQUEST' => 'true',
        ]);

        $this->assertFalse(HtmxTable::isFragmentRequest($request));
    }

    public function test_non_htmx_requests_are_not_treated_as_fragment_requests(): void
    {
        $this->assertFalse(HtmxTable::isFragmentRequest(Request::create('/items')));
    }

    public function test_history_restore_without_htmx_header_is_not_a_fragment_request(): void
    {
        $request = Request::create('/items', 'GET', [], [], [], [
            'HTTP_HX_HISTORY_RESTORE_REQUEST' => 'true',
        ]);

        $this->assertFalse(HtmxTable::isFragmentRequest($request));
    }
}
