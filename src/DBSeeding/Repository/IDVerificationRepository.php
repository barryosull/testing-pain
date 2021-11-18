<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Repository;

use Barryosull\TestingPain\DBSeeding\Model\IDVerification;

interface IDVerificationRepository
{
    public function store(IDVerification $id_verification): void;
}