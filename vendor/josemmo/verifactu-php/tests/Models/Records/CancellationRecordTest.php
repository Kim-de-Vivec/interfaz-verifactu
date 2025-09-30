<?php
namespace josemmo\Verifactu\Tests\Models\Records;

use DateTimeImmutable;
use josemmo\Verifactu\Exceptions\InvalidModelException;
use josemmo\Verifactu\Models\Records\CancellationRecord;
use josemmo\Verifactu\Models\Records\InvoiceIdentifier;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;

final class CancellationRecordTest extends TestCase {
    #[DoesNotPerformAssertions]
    public function testRequiresPreviousInvoice(): void {
        $record = new CancellationRecord();
        $record->invoiceId = new InvoiceIdentifier();
        $record->invoiceId->issuerId = '89890001K';
        $record->invoiceId->invoiceNumber = '12345679/G34';
        $record->invoiceId->issueDate = new DateTimeImmutable('2024-01-01');
        $record->previousInvoiceId = null; // This is not allowed
        $record->previousHash = null; // This is not allowed
        $record->hashedAt = new DateTimeImmutable('2024-01-01T19:20:40+01:00');
        $record->hash = $record->calculateHash();
        try {
            $record->validate();
            $this->fail('Did not throw exception for missing previous invoice');
        } catch (InvalidModelException $e) {
            // Expected, ignore
        }

        $record->previousInvoiceId = new InvoiceIdentifier();
        $record->previousInvoiceId->issuerId = '89890001K';
        $record->previousInvoiceId->invoiceNumber = '12345679/G34';
        $record->previousInvoiceId->issueDate = new DateTimeImmutable('2024-01-01');
        $record->hash = $record->calculateHash();
        try {
            $record->validate();
            $this->fail('Did not throw exception for missing previous hash');
        } catch (InvalidModelException $e) {
            // Expected, ignore
        }
    }

    public function testCalculatesHashForOtherRecords(): void {
        $record = new CancellationRecord();
        $record->invoiceId = new InvoiceIdentifier();
        $record->invoiceId->issuerId = '89890001K';
        $record->invoiceId->invoiceNumber = '12345679/G34';
        $record->invoiceId->issueDate = new DateTimeImmutable('2024-01-01');
        $record->previousInvoiceId = new InvoiceIdentifier();
        $record->previousInvoiceId->issuerId = '89890001K';
        $record->previousInvoiceId->invoiceNumber = '12345679/G34';
        $record->previousInvoiceId->issueDate = new DateTimeImmutable('2024-01-01');
        $record->previousHash = 'F7B94CFD8924EDFF273501B01EE5153E4CE8F259766F88CF6ACB8935802A2B97';
        $record->hashedAt = new DateTimeImmutable('2024-01-01T19:20:40+01:00');
        $record->hash = $record->calculateHash();
        $this->assertEquals('177547C0D57AC74748561D054A9CEC14B4C4EA23D1BEFD6F2E69E3A388F90C68', $record->hash);
        $record->validate();
    }
}
