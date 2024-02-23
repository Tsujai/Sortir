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

    public function findOneBySomeField($site,$search,$firstDate,$secondDate,$moiQuiOrganise,$moiInscrit,$moiPasInscrit,$sortiesPassees): array
    {
        $query=$this->createQueryBuilder('sortie')
            ->andWhere('sortie.site = :value')
            ->setParameter('value', $site)
            ->andWhere('sortie.nom like :value')
            ->setParameter('value', '%' . $search . '%')
            ->andWhere('sortie.dateHeureDebut <= :value')
            ->setParameter('value', $firstDate)
            ->andWhere('sortie.dateHeureDebut >= :value')
            ->setParameter('value', $secondDate)
            ->andWhere('sortie.organisateur = :value')
            ->setParameter('value', $moiQuiOrganise)
            ->andWhere(':value in sortie.participants')
            ->setParameter('value', $moiInscrit)
            ->andWhere(':value not in sortie.participants')
            ->setParameter('value', $moiPasInscrit)
            ->andWhere('sortie.etat.nom = :value')
            ->setParameter('value', $sortiesPassees)
            ->getQuery()
            ->getResult();

        return $query;
    }
}
