<?php
declare(strict_types=1);

namespace App\Controller\MicroPost;

use App\Entity\Comment;
use App\Entity\User;
use App\Helper\FlashType;
use App\Security\Voter\CommentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/micro-post/{_locale<%app.supported_locales%>}/comment")
 * @IsGranted(User::ROLE_USER)
 */
class CommentController extends AbstractController
{
    private $em;
    private $translator;

    public function __construct(EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @Route("/del/{uuid}", methods={"get"}, name="micro_post_comment_del")
     */
    public function del(Comment $comment): RedirectResponse
    {
        $this->denyAccessUnlessGranted(
            CommentVoter::COMMENT_DEL_OWNER_OR_ADMIN,
            $comment,
            $this->translator->trans('micro-post.comments.del.cant_del_message')
        );

        if (!$comment->isDeleted()) {
            $this->em->remove($comment);
            $this->em->flush();

            $contentPart = mb_substr($comment->getContent(), 0, 30) . '...';
            $this->addFlash(
                FlashType::SUCCESS,
                $this->translator->trans('micro-post.comments.del.success_message', ['%content_part%' => $contentPart])
            );
        } else {
            $message = $this->translator->trans('micro-post.comments.del.is_deleted_message');

            throw new NotFoundHttpException($message);
        }

        return $this->redirectToRoute('micro_post_view', ['uuid' => $comment->getPost()->getUuid()]);
    }

    /**
     * @Route("/restore/{uuid}", methods={"get"}, name="micro_post_comment_restore")
     * @IsGranted(User::ROLE_ADMIN)
     */
    public function restore(Comment $comment): RedirectResponse
    {
        $comment->setDeleteAt(null);
        $this->em->persist($comment);
        $this->em->flush();

        $contentPart = mb_substr($comment->getContent(), 0, 30) . '...';
        $this->addFlash(
            FlashType::SUCCESS,
            $this->translator->trans('micro-post.comments.restore.success_message', ['%content_part%' => $contentPart])
        );

        return $this->redirectToRoute('micro_post_view', ['uuid' => $comment->getPost()->getUuid()]);
    }

}
