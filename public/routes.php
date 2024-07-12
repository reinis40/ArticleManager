<?php

return [
      ['GET', '/', [App\Controller\ArticleController::class, 'list']],
      ['GET', '/articles', [App\Controller\ArticleController::class, 'list']],
      ['GET', '/articles/{id}', [App\Controller\ArticleController::class, 'show']],
      ['GET', '/articles/{id}/like', [App\Controller\ArticleController::class, 'like']],
      ['POST', '/articles/{id}/comment', [App\Controller\CommentController::class, 'addComment']],
      ['GET', '/comments/{id}/like', [App\Controller\CommentController::class, 'likeComment']],
      ['GET', '/create-article', [App\Controller\ArticleController::class, 'createForm']],
      ['POST', '/create-article', [App\Controller\ArticleController::class, 'create']],
      ['GET', '/articles/{id}/edit', [App\Controller\ArticleController::class, 'edit']],
      ['POST', '/articles/{id}/update', [App\Controller\ArticleController::class, 'update']],
      ['POST', '/articles/{id}/delete', [App\Controller\ArticleController::class, 'delete']]
];
