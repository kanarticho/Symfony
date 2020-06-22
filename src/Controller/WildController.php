<?php
// src/Controller/WildController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Category;
use App\Entity\Episode;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wild", name="wild_")
 */

Class WildController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        if (!$programs) {
            throw $this->createNotFoundException(
                'No program found in program\'s table'
            );
        }

        return $this->render(
            'wild/index.html.twig',
            ['programs' => $programs]
        );
    }

    /**
     * @Route("/show/{slug}", requirements={"slug"="^[a-z0-9-]+$"}, defaults={"slug"=1}, name="show")
     */

    public function show(?string $slug): Response
    {
        if (!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'Le programme ' . $slug . ' n\'est pas trouvé'
            );
        }

        return $this->render('wild/show.html.twig', [
            'program' => $program,
            'slug' => $slug,
        ]);
    }

    /**
     *
     * @param string $categoryName
     * @Route("/wild/category/{categoryName}", name="show_category")
     * @return Response
     */
    public function showByCategory(string $categoryName): Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(
                ['name' => $categoryName]
            );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(
                ['category' => $category],
                ['id' => 'DESC'],
                3
            );

        return $this->render('wild/category.html.twig', [
            'programs' => $program,
        ]);
    }

    /**
     *
     * @param string $slug
     * @Route("/program/{slug<^[a-z0-9-]+$>}", defaults={"slug" = null}, name="show_program")
     * @return Response
     */
    public function showByProgram(?string $slug = 'Aucune saison selectionnée'): Response
    {
        if(!$slug) {
            throw $this
                ->createNotFoundException('No slug has been sent to find a program in program\'s table.');
        }
        $slug = preg_replace(
            '/-/',
            ' ', ucwords(trim(strip_tags($slug)), "-")
        );
        $program = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findOneBy(['title' => mb_strtolower($slug)]);
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with ' .$slug.' title.'
            );
        }

        return $this->render('wild/program.html.twig', [
            'program' => $program,
            'slug' => $slug
        ]);
    }

    /**
     * @param int $id
     * @Route ("/season/{id}", name="show_season")
     * @return Response
     */
    public function showBySeason(int $id): Response
    {
        $season = $this->getDoctrine()
            ->getRepository(Season::class)
            ->findOneBy(['id' => $id]);
        if (!$id) {
            throw $this->createNotFoundException(
                'No season with ' .$id.' identifier.'
            );
        }
        $program = $season->getProgram();
        $slug = preg_replace(
            '/ /',
            '-', strtolower($program->getTitle())
        );

        $episodes = $this->getDoctrine()
            ->getRepository(Episode::class)
            ->findBy(['season' => $id], ['id' => 'asc']);
        if (!$episodes) {
            throw $this->createNotFoundException(
                'No episode found in episode\'s table.'
            );
        }

        return $this->render('wild/season.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $episodes,
            'slug' => $slug
        ]);
    }


    /**
     * @Route("/episode/{id}", name="episode")
     * @param Episode $episodes
     * @return Response
     */
    public function showEpisode(Episode $episodes): Response
    {
        $season = $episodes->getSeason();
        $program = $season->getProgram();
        return $this->render('wild/episode.html.twig',[
            'program' => $program,
            'episode' => $episodes,
            'season' => $season
        ]);
    }
}

