<?php

namespace IWGB\Join\Domain;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Exception;
use Ramsey\Uuid\Uuid;

class UuidGenerator extends AbstractIdGenerator {

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function generate(EntityManager $em, $entity) {
        return Uuid::uuid4()->toString();
    }
}