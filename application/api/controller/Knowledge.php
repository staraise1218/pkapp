<?php

namespace app\api\controller;
use think\Db;

class Knowledge extends Base {

	public function __construct(){
		// 设置所有方法的默认请求方式
		$this->method = 'POST';

		parent::__construct();
	}

    public function zhishikuPage(){
        $user_id = I('user_id');

        // 获取用户的年级
        $user = Db::name('users')
            ->where('user_id', $user_id)
            ->field('grade_id')
            ->find();

        // 获取年级列表
        $gradeList = Db::name('grade')
            ->where('is_open', 1)
            ->where('is_delete', 0)
            ->column('id, title');

        $result['use'] = $user;
        $result['gradeList'] = $gradeList;
        response_success($result);
    }

    /**
     * [topics lesson_type 1 语文 2 数学 3 英语]
     * [grade_type 年级id]
     * [level 级别]
     * @return [type] [description]
     */
    public function getList(){
        $lesson_type = I('lesson_type');
        $grade_type = I('grade_type');
        $level = I('level');
        // $slq = "SELECT id, title, a, b, c, d, answer FROM `tp_knowledge` WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `tp_knowledge`))) and is_open=1 and is_delete=0 and lesson_type = ? and grade_type=? and level=?  ORDER BY id ASC LIMIT 5"；
        // $sql = "SELECT t1.id, t1.title FROM `tp_knowledge`  AS t1 JOIN (SELECT ROUND(RAND() * (SELECT MAX(id) FROM `tp_knowledge`)) AS id) AS t2 WHERE t1.id >= t2.id AND t1.lesson_type=? AND t1.grade_type=? AND t1.level=? ORDER BY t1.id ASC LIMIT 5";
        $sql = "SELECT * FROM `tp_knowledge`WHERE id >= (SELECT floor(RAND() * (SELECT MAX(id) FROM `tp_knowledge`))) AND lesson_type=? AND grade_type=? AND level=? ORDER BY id LIMIT 5";
        $list = Db::query($sql, [$lesson_type, $grade_type, $level]);


        response_success($list);
    }

    /** 
     * [answer 知识库答题]
     * @param [type] $[answer] [<json对象 [{"knowledge_id":"a","answer":"a"}]>]
     * @return [type] [description]
     */
    public function answer(){
        $user_id = I('user_id');
        $answer = I('answer');

        $answer = json_decode(html_entity_decode($answer), true);
        if(empty($answer)) response_error('', '没有数据');

        $knowledge_id_arr = [];
        foreach ($answer as $item) {
            $knowledge_id_arr[] = $item['knowledge_id'];
        }

        // 查找知识
        $knowledgeList = Db::name('knowledge')
            ->where('id', 'in', $knowledge_id_arr)
            ->column('id, answer');
        foreach ($answer as &$item) {
            if (isset($knowledgeList[$item['knowledge_id']])) {
                if($item['answer'] == $knowledgeList[$item['knowledge_id']]) {
                    $item['is_right'] = 1;
                } else {
                    $item['is_right'] = 0;
                }
            } else {
                $item['is_right'] = 0;
            }
            $item['right_answer'] = $knowledgeList[$item['knowledge_id']];

            $data = array(
                'user_id' => $user_id,
                'knowledge_id' => $item['knowledge_id'],
                'is_right' => $item['is_right'],
                'add_time' => time(),
            );
            // 记录答题日志
            Db::name('answer_record')->insert($data);
        }


        response_success($answer);
    }
}