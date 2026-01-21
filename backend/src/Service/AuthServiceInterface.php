<?php
namespace App\Service;

use App\Dto\AuthDto;
use App\Entity\User;

interface AuthServiceInterface
{
    public function getUser(AuthDto $dto): ?User;
}
