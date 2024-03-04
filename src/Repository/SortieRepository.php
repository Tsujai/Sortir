<?php

namespace App\Repository;

use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 *
 * @method Sortie|null find($idSortie, $lockMode = null, $lockVersion = null)
 * @method Sortie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sortie[]    findAll()
 * @method Sortie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

//    /**
//     * @return Sortie[] Returns an array of Sortie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function findOneBySomeField(array $filters): array
    {
        $query = $this->createQueryBuilder('s');

        if (!empty($filters['site'])) {
            $query->andWhere('s.site = :site')
                ->setParameter('site', $filters['site']);
        }
        if (!empty($filters['nom'])) {
            $query->andWhere('s.nom like :nom')
                ->setParameter('nom', '%' . $filters['nom'] . '%');
        }
        if (!empty($filters['firstDate'])) {
            $query->andWhere('s.dateHeureDebut <= :firstDate')
                ->setParameter('firstDate', ($filters['firstDate'])->format('Y-m-d'));
        }
        if (!empty($filters['secondDate'])) {
            $query->andWhere('s.dateHeureDebut >= :secondDate')
                ->setParameter('secondDate', ($filters['secondDate'])->format('Y-m-d'));
        }
        if (!empty($filters['moiQuiOrganise'])) {
            $query->andWhere('s.organisateur = :moiQuiOrganise')
                ->setParameter('moiQuiOrganise', $filters['moiQuiOrganise']);
        }
        if (!empty($filters['moiInscrit'])) {
            $query->join('s.participants', 'p1')
                ->andWhere('p1.id = :moiInscrit')
                ->setParameter('moiInscrit', $filters['moiInscrit']);
        }

        if (!empty($filters['moiPasInscrit'])) {
            $query->leftJoin('s.participants', 'p2')
                ->andWhere('p2.id != :moiPasInscrit OR p2.id IS NULL')
                ->setParameter('moiPasInscrit', $filters['moiPasInscrit']);
        }
        if (!empty($filters['sortiesPassees'])) {
            $query->andWhere('s.etat = :sortiesPassees')
                ->setParameter('sortiesPassees', $filters['sortiesPassees']);
        }

        return $query
                ->getQuery()
                ->getResult();
    }
}
