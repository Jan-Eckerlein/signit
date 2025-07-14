<?php

namespace App\Enums;

enum PdfProcessStatus: string
{
    case PDF_MISSING = 'pdf_missing';
    case PDF_REGENERATING = 'pdf_regenerating';
    case PDF_REGENERATED = 'pdf_regenerated';
    case PDF_REGENERATING_FAILED = 'pdf_regeneration_failed';
    case PDF_SIGNING = 'pdf_signing';
    case PDF_SIGNED = 'pdf_signed';
    case PDF_SIGNING_FAILED = 'pdf_signing_failed';
    case PDF_SIGNING_RETRYING = 'pdf_signing_retrying';
    case PDF_SIGNING_RETRY_FAILED = 'pdf_signing_retry_failed';
} 