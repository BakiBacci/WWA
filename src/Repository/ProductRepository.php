<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    private PaginatorInterface $paginator;

    public function __construct(ManagerRegistry $registry, PaginatorInterface $paginator)
    {
        parent::__construct($registry, Product::class);
        $this->paginator = $paginator;
    }

    public function paginateProducts(int $page): PaginationInterface
    {
        return $this->paginator->paginate($this->createQueryBuilder('p'), $page, 10);
    }

    public function paginateProductsOrderByUpdatedAt(int $page): PaginationInterface
    {
        return $this->paginator->paginate($this->createQueryBuilder('p')->orderBy('p.updatedAt', 'DESC'), $page, 10);
    }

    public function findProductWithCategoryBySlug(string $slug): ?Product
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->andWhere('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function search(string $query): array
     {
    return $this->createQueryBuilder('p')
        ->where('p.name LIKE :q')
        ->setParameter('q', '%' . $query . '%')
        ->getQuery()
        ->getResult();
}

}
