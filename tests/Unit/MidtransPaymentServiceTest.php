<?php

namespace Tests\Unit;

use App\Services\MidtransPaymentService;
use Tests\TestCase;

class MidtransPaymentServiceTest extends TestCase
{
    public function test_it_validates_midtrans_notification_signatures(): void
    {
        $serverKey = 'sandbox-test-key';
        config(['services.midtrans.server_key' => $serverKey]);

        $notification = [
            'order_id' => 'RESI-123',
            'status_code' => '200',
            'gross_amount' => '150000.00',
        ];
        $notification['signature_key'] = hash(
            'sha512',
            $notification['order_id'] . $notification['status_code'] . $notification['gross_amount'] . $serverKey
        );

        $service = new MidtransPaymentService();

        $this->assertTrue($service->isValidNotificationSignature($notification));

        $notification['gross_amount'] = '1.00';
        $this->assertFalse($service->isValidNotificationSignature($notification));
    }

    public function test_it_requires_exact_rupiah_amount_match(): void
    {
        $service = new MidtransPaymentService();

        $this->assertTrue($service->isMatchingAmount('150000.00', 150000));
        $this->assertFalse($service->isMatchingAmount('150001.00', 150000));
        $this->assertFalse($service->isMatchingAmount('150000', 150000));
    }
}
