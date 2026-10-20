<?php

namespace App\Doctrine\Repository\Sylius;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\ProductReviewRepository as BaseProductReviewRepository;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\Taxon;
use Sylius\Component\Review\Model\ReviewInterface;

class ProductReviewRepository extends BaseProductReviewRepository implements ProductReviewRepositoryInterface
{
    public function createListQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->leftJoin('o.order', 'command');
    }

    public function csvListQueryBuilder(?array $criteria = null): IterableResult
    {
        $qb = $this->createQueryBuilder('o');

        if (isset($criteria['title']['value']) and '' !== $criteria['title']['value']) {
            $qb->andWhere('o.title = :title')
                ->setParameter('title', $criteria['title']['value']);
        }

        if (isset($criteria['remercie']['remercie']) and '' !== $criteria['remercie']['remercie']) {
            $qb->andWhere('o.remercie = :remercie');

            if ('1' === $criteria['remercie']['remercie']) {
                $qb->setParameter('remercie', true);
            } else {
                $qb->setParameter('remercie', false);
            }
        }

        if (isset($criteria['rating']['rating']) and '' !== $criteria['rating']['rating']) {
            $qb->andWhere('o.rating = :rating')
                ->setParameter('rating', $criteria['rating']['rating']);
        }

        if (isset($criteria['status']['status']) and '' !== $criteria['status']['status']) {
            $qb->andWhere('o.status = :status')
                ->setParameter('status', $criteria['status']['status']);
        }

        if (isset($criteria['reviewSubject']) and $criteria['reviewSubject']) {
            $qb->andWhere('o.reviewSubject = :reviewSubject')
                ->setParameter('reviewSubject', $criteria['reviewSubject']);
        }

        if (isset($criteria['locale']['locale']) and $criteria['locale']['locale']) {
            $qb->andWhere('o.locale = :locale')
                ->setParameter('locale', $criteria['locale']['locale']);
        }

        if (isset($criteria['order']['value']) and $criteria['order']['value']) {
            $qb->andWhere('o.order = :order')
                ->setParameter('order', $criteria['order']['value']);
        }

        $qb->orderBy('o.createdAt', 'DESC');

        return $qb->getQuery()->iterate();
    }

    public function findUnrejectedByProductCodeAndChannel(string $code, string $locale, ChannelInterface $channel, ?array $filter, ?array $allQuestions): array
    {
        $query = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->innerJoin('o.author', 'author')
            ->leftJoin('o.answers', 'answers')
            ->leftJoin('answers.answerChoices', 'answerChoice')
            ->leftJoin('answerChoice.translations', 'translation', 'WITH', 'translation.locale = :locale')
            ->andWhere('product.code = :code')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.status = :status')
            ->andWhere('o.locale = :locale')
            ->addOrderBy('o.createdAt', 'DESC')
            ->setParameter('locale', $locale)
            ->setParameter('code', $code)
            ->setParameter('channel', $channel)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED);

        if (!empty($filter['rating'])) {
            $query->andWhere('o.rating = :rating')
                ->setParameter('rating', $filter['rating']);
        } elseif (!empty($filter['minRating'])) {
            $query->andWhere('o.rating >= :minRating')
                ->setParameter('minRating', $filter['minRating']);
        }

        if (!empty($filter['ageMin'])) {
            $yearMin = date('Y') - $filter['ageMin'];
            $ageMin = new \DateTime();
            $ageMin->setDate($yearMin, date('m'), date('d'));
            $ageMin->setTime(0, 0, 0, 0);

            $query->andWhere('author.birthday <= :ageMin')
                ->setParameter('ageMin', $ageMin);
        }

        if (!empty($filter['ageMax'])) {
            $yearMax = date('Y') - $filter['ageMax'];
            $ageMax = new \DateTime();
            $ageMax->setDate($yearMax, date('m'), date('d'));
            $ageMax->setTime(0, 0, 0, 0);

            $query->andWhere('author.birthday >= :ageMax')
                ->setParameter('ageMax', $ageMax);
        }

        if (!empty($filter['comments'])) {
            $query
                ->andWhere('o.comment IS NOT NULL')
                ->andWhere("o.comment != ''");
        }

        $i = 1;
        foreach ($allQuestions as $question) {
            $query->innerJoin('o.answers', 'answers' . $i)
                ->innerJoin('answers' . $i . '.question', 'question' . $i)
                ->andWhere('question' . $i . '.question = :question' . $i)
                ->andWhere('answers' . $i . '.answer = :answer' . $i)
                ->setParameter('question' . $i, $question['question'])
                ->setParameter('answer' . $i, $question['answer']);

            ++$i;
        }

        if (!empty($filter['star'])) {
            $query->orderBy('o.star', 'DESC')
                ->addOrderBy('o.createdAt', 'DESC');
        } else {
            $query->orderBy('o.createdAt', 'DESC');
        }

        return $query->getQuery()
            ->getResult();
    }

    public function getNoRemercieAndAccepted($product = null, $mode = 'array')
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.reviewSubject', 'product')
            ->andWhere('product.code NOT LIKE :phenix')
            ->andWhere('o.remercie IS NULL')
            ->andWhere('o.status = :status')
            ->setParameter('status', 'accepted')
            ->setParameter('phenix', '%phenix%')
            ->orderBy('o.createdAt', 'DESC');

        if (null !== $product) {
            $qb->andWhere('product = :product')
                ->setParameter('product', $product);
        }

        if ('iterate' === $mode) {
            return $qb->getQuery()->iterate();
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function updateNoRemercieAndAccepted($products)
    {
        $now = date('Y-m-d H:i:s');

        $qb = $this->createQueryBuilder('o')
            ->update()
            ->set('o.remercie', 'true')
            ->set('o.remercieDate', ':remercieDate')
            ->andWhere('o.remercie IS NULL')
            ->andWhere('o.status = :status')
            ->setParameter('remercieDate', $now)
            ->setParameter('status', 'accepted');

        if (!empty($products)) {
            $qb->andWhere('o.reviewSubject IN (:products)')
                ->setParameter('products', $products);
        }

        return $qb->getQuery()->getResult();
    }

    public function getAverageRatingsAndCountReviews(int $taxonId): array
    {
        $qb = $this->createQueryBuilder('o')
            ->select('avg(o.rating) as averageRating, count(o) as numberOfRating')
            ->innerJoin('o.reviewSubject', 'product')
            ->innerJoin('product.productTaxons', 'productTaxon')
            ->andWhere('productTaxon.taxon = :taxonId')
            ->setParameter('taxonId', $taxonId);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getReviewsByTaxon(Taxon $taxon, string $locale, ChannelInterface $channel, ?array $filter): array
    {
        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->innerJoin('product.translations', 'translation')
            ->innerJoin('product.productTaxons', 'productTaxon')
            ->andWhere('translation.locale = :locale')
            ->andWhere(':channel MEMBER OF product.channels')
            ->andWhere('o.status = :status')
            ->andWhere('productTaxon.taxon = :taxonId')
            ->setParameter('locale', $locale)
            ->setParameter('channel', $channel)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('taxonId', $taxon->getId())
            ->orderBy('o.createdAt', 'DESC');

        if (!empty($filter['comments'])) {
            $qb
                ->andWhere('o.comment IS NOT NULL')
                ->andWhere("o.comment != ''");
        }

        return $qb->getQuery()->getResult();
    }

    public function getReviewForLastMonth($d)
    {
        $d->sub(new \DateInterval('P1M'));
        $start = $d->format('Y-m') . '-01';
        $end = $d->format('Y-m-t');

        $qb = $this->createQueryBuilder('o')
            ->innerJoin('o.reviewSubject', 'product')
            ->innerJoin('product.translations', 'translation')
            ->innerJoin('o.author', 'author')
            ->andWhere('translation.locale = :locale')
            ->andWhere('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->setParameter('locale', 'fr_FR')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('o.rating', 'DESC')
            ->addOrderBy('author.lastName', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getIterableRssReviews(int $index, int $limit): iterable
    {
        $query = $this->createRssReviewQueryBuilder()
            ->setFirstResult($index)
            ->setMaxResults($limit);

        return $query->getQuery()->toIterable();
    }

    public function countRssReview(): int
    {
        return $this->createRssReviewQueryBuilder()
            ->select('count(r.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function createRssReviewQueryBuilder(): QueryBuilder
    {
        return $this->createQueryBuilder('r')
            ->distinct()
            ->where('r.status = :accepted')
            ->leftJoin('r.product', 'p')
            ->leftJoin('p.productTaxons', 'pt')
            ->leftJoin('pt.taxon', 't')
            ->leftJoin('r.author', 'a')
            ->andWhere('r.product is not null')
            ->andWhere('t.code = :code')
            ->andWhere('r.comment is not null and r.comment <> \'\'')
            ->andWhere('r.title is not null and r.title <> \'\'')
            ->andWhere('a.firstName is not null and a.firstName <> \'\'')
            ->setParameter('accepted', ReviewInterface::STATUS_ACCEPTED)
            ->setParameter('code', 'google-shopping');
    }

    /**
     * @return float|int|mixed|string|null
     *
     * @throws NonUniqueResultException
     */
    public function findLastForProduct(ProductInterface $product)
    {
        $builder = $this->createQueryBuilder('r')
            ->andWhere('r.product = :product')
            ->setParameter('product', $product)
            ->addOrderBy('r.createdAt', 'DESC')
            ->setMaxResults(1);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @return float|int|mixed|string|null
     *
     * @throws NonUniqueResultException
     */
    public function countAllForProducts(array $excludedCodes = [])
    {
        $qb = $this->createQueryBuilder('r')
            ->select('avg(r.rating) as averageRating, count(r) as numberOfRating');

        if (!empty($excludedCodes)) {
            $qb->leftJoin('r.product', 'p')
                ->andWhere('p.code NOT IN (:codes)')
                ->andWhere('r.status = :status')
                ->setParameter('codes', $excludedCodes)
                ->setParameter('status', ReviewInterface::STATUS_ACCEPTED);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return float|int|mixed|string|null
     *
     * @throws NonUniqueResultException
     */
    public function countAllForProduct(ProductInterface $product)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('avg(r.rating) as averageRating, count(r) as numberOfRating')
            ->andWhere('r.product = :product')
            ->andWhere('r.status = :status')
            ->setParameter('product', $product)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function countRatingForProduct(ProductInterface $product): int
    {
        $sql = 'SELECT COUNT(id) FROM sylius_product_review WHERE product_id = :product AND status = :status';
        $result = $this->getEntityManager()->getConnection()->executeQuery($sql, [
            'product' => $product->getId(),
            'status' => ReviewInterface::STATUS_ACCEPTED,
        ]);

        return (int) $result->fetchOne();
    }

    public function getReviewDistributionForProduct(ProductInterface $product)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.rating, COUNT(r.id) as count')
            ->andWhere('r.product = :product')
            ->andWhere('r.status = :status')
            ->groupBy('r.rating')
            ->setParameter('product', $product)
            ->setParameter('status', ReviewInterface::STATUS_ACCEPTED);

        return $qb->getQuery()->getResult();
    }
}
