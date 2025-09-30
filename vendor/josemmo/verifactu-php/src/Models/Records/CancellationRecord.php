<?php
namespace josemmo\Verifactu\Models\Records;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Registro de anulación de una factura
 *
 * @field RegistroAnulacion
 */
class CancellationRecord extends Record {
    /**
     * @inheritDoc
     */
    public function calculateHash(): string {
        // NOTE: Values should NOT be escaped as that what the AEAT says ¯\_(ツ)_/¯
        $payload  = 'IDEmisorFacturaAnulada=' . $this->invoiceId->issuerId;
        $payload .= '&NumSerieFacturaAnulada=' . $this->invoiceId->invoiceNumber;
        $payload .= '&FechaExpedicionFacturaAnulada=' . $this->invoiceId->issueDate->format('d-m-Y');
        $payload .= '&Huella=' . ($this->previousHash ?? '');
        $payload .= '&FechaHoraHusoGenRegistro=' . $this->hashedAt->format('c');
        return strtoupper(hash('sha256', $payload));
    }

    #[Assert\Callback]
    final public function validateEnforcePreviousInvoice(ExecutionContextInterface $context): void {
        if ($this->previousInvoiceId === null) {
            $context->buildViolation('Previous invoice ID is required for all cancellation records')
                ->atPath('previousInvoiceId')
                ->addViolation();
        }
        if ($this->previousHash === null) {
            $context->buildViolation('Previous hash is required for all cancellation records')
                ->atPath('previousHash')
                ->addViolation();
        }
    }
}
