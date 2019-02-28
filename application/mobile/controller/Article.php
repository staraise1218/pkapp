<?php

namespace app\mobile\controller;

use think\Db;
 
class Article extends Base
{
    /**
     * 文章内容页
     */
    public function detail()
    {
        $article_id = input('article_id/d');
        $article = Db::name('article')->where("article_id", $article_id)->find();

        $article['content'] = htmlspecialchars_decode($article['content']);
        $this->assign('article', $article);
        return $this->fetch();
    }

    public function questionDetail(){
        $article_id = input('article_id/d', 1);
        $article = Db::name('article')->where("article_id", $article_id)->find();
        $article['content'] = htmlspecialchars_decode($article['content']);
        $this->assign('article', $article);
        return $this->fetch();
    }
}