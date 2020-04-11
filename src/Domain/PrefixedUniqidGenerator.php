<?php

namespace Iwgb\Join\Domain;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Id\AbstractIdGenerator;
use Exception;

class PrefixedUniqidGenerator extends AbstractIdGenerator {

    /**
     * {@inheritdoc}
     * @throws Exception
     */
    public function generate(EntityManager $em, $entity) {
        return $entity::$prefix . uniqid();
    }
}