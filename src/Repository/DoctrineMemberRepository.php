<?php

namespace Avid\CandidateChallenge\Repository;

use Avid\CandidateChallenge\Model\Address;
use Avid\CandidateChallenge\Model\Email;
use Avid\CandidateChallenge\Model\Height;
use Avid\CandidateChallenge\Model\Member;
use Avid\CandidateChallenge\Model\Weight;
use Doctrine\DBAL\Types\Type;

/**
 * @author Kevin Archer <kevin.archer@avidlifemedia.com>
 */
final class DoctrineMemberRepository extends DoctrineRepository implements MemberRepository
{
    const CLASS_NAME = __CLASS__;
    const TABLE_NAME = 'members';
    const ALIAS = 'member';

    /**
     * @param Member $member
     *
     * @return int Affected rows
     */
    public function add($member)
    {
        return $this->getConnection()->insert($this->getTableName(), $this->extractData($member), $this->getDataTypes());
    }

    /**
     * @param Member $member
     *
     * @return int Affected rows
     */
    public function update($member)
    {
        return $this->getConnection()->update($this->getTableName(), $this->extractData($member), array('username' => $member->getUsername()), $this->getDataTypes());
    }

    /**
     * @param Member $member
     *
     * @return int
     */
    public function remove($member)
    {
        return $this->getConnection()->delete($this->getTableName(), array('username' => $member->getUsername()));
    }

    /**
     * @param string $username
     *
     * @return Member|null
     */
    public function findByUsername($username)
    {
        $qb = $this->getBaseQuery()
            ->where('username = :username')
            ->setParameter('username', $username);

        $result = $this->execute($qb)->fetchAll();

        return isset($result[0]) ? $this->hydrate($result[0]) : null;
    }

    /**
     * @param string $keyword
     * @param int $first
     * @param int $max
     *
     * @return Member[]
     */
    public function search($keyword, $first = 0, $max = null)
    {
        $qb = $this->getBaseQuery($first, $max)
            ->where('username like :username')
            ->setParameter('username', "%{$keyword}%");

        return $this->hydrateAll($this->execute($qb)->fetchAll());
    }

    /**
     * @param string $keyword
     *
     * @return int
     */
    public function getSearchCount($keyword)
    {
        $qb = $this->getBaseQuery()
            ->select('count(*) as count')
            ->where('username like :username')
            ->setParameter('username', "%{$keyword}%");

        $result = $this->execute($qb)->fetch();

        return (int) $result['count'];
    }

    /**
     * @return int
     */
    public function count()
    {
        $qb = $this->createQueryBuilder()
            ->select('count(*) as count')
            ->from($this->getTableName(), $this->getAlias());

        $result = $this->execute($qb)->fetch();

        return (int) $result['count'];
    }

    /**
     * @param int $first
     * @param int $max
     *
     * @return object
     */
    public function findAll($first = 0, $max = null)
    {
        return $this->hydrateAll($this->execute($this->getBaseQuery($first, $max))->fetchAll());
    }

    /**
     * @param array $row
     *
     * @return Member
     */
    protected function hydrate(array $row)
    {
        return new Member(
            $row['username'],
            $row['password'],
            new Address($row['country'], $row['province'], $row['city'], $row['postal_code']),
            new \DateTime($row['date_of_birth']),
            $row['limits'],
            new Height($row['height']),
            new Weight($row['weight']),
            $row['body_type'],
            $row['ethnicity'],
            new Email($row['email'])
        );
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        return self::TABLE_NAME;
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        return self::ALIAS;
    }

    /**
     * @param Member $member
     *
     * @return array
     */
    private function extractData($member)
    {
        return [
            'username' => $member->getUsername(),
            'password' => $member->getPassword(),
            'country' => $member->getAddress()->getCountry(),
            'province' => $member->getAddress()->getProvince(),
            'city' => $member->getAddress()->getCity(),
            'postal_code' => $member->getAddress()->getPostalCode(),
            'date_of_birth' => $member->getDateOfBirth(),
            'limits' => $member->getLimits(),
            'height' => $member->getHeight(),
            'weight' => $member->getWeight(),
            'body_type' => $member->getBodyType(),
            'ethnicity' => $member->getEthnicity(),
            'email' => $member->getEmail(),
        ];
    }

    /**
     * @return array
     */
    private function getDataTypes()
    {
        return [
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::DATE,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
            Type::STRING,
        ];
    }
}
