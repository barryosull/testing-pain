<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Repository;

use Barryosull\TestingPain\DBSeeding\Model\IDVerification;

class IDVerificationRepositoryActiveRecord implements IDVerificationRepository
{
    public function store(IDVerification $id_verification): void
    {
        $id_verification->store();
    }
}