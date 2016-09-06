<?php

/////////////////////////////////////////////////////////////////////////////
	// 这个文件是 美容整形社区法识的一部分
	//
	// Copyright (c) 2015 – 2020  QQ:1149100178
	// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
	// 或者联系qq:easyWe(2504585798)获得详细信息。

class LawyerkownAction extends CommonAction { //按逻辑  instructions  和  details 要分表出去

    private $create_fields = array( 'author','source','orderby','cate_id', 'intro', 'title', 'photo',  'is_hot', 'is_new', 'is_chose','audit','details','is_top','closed');
    private $edit_fields = array( 'lawyerkown_id','author','source','orderby','cate_id', 'intro', 'title', 'photo',  'is_hot', 'is_new', 'is_chose','audit','details','is_top','closed');
    public function _initialize() {
        parent::_initialize();
        $this->Lawyerkowncates = D('Lawyerkowncate')->fetchAll();
        $this->assign('cates', $this->Lawyerkowncates);
    }

    public function index() {
        $Lawyerkown = D('Lawyerkown');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Lawyerkowncate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
      
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
        }
        $count = $Lawyerkown->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Lawyerkown->where($map)->order(array('lawyerkown_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
       /*  foreach ($list as $k => $val) {
            if ($val['hospital_id']) {
                $hospital_ids[$val['hospital_id']] = $val['hospital_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val = $Lawyerkown->_format($val);
            $list[$k] = $val;
        }
        if ($hospital_ids) {
            $this->assign('hospitals', D('Hospital')->ItemsByIds($hospital_ids));
        } */
        $this->assign('cates', D('Lawyerkowncate')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

  
        public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Lawyerkown');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if (empty($details)) {
                $this->baoError('法识详情不能为空');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('详细内容含有敏感词：' . $words);
            }
           
            if ($words = D('Sensitive')->checkWords($instructions)) {
                $this->baoError('购买须知含有敏感词：' . $words);
            }
             $data['details']= $details;
            if ($lawyerkown_id = $obj->add($data)) {
               /*  $wei_pic = D('Weixin')->getCode($lawyerkown_id, 2); //法识类型是2
                $obj->save(array('lawyerkown_id' => $lawyerkown_id, 'wei_pic' => $wei_pic));
                D('Lawyerkowndetails')->add(array('lawyerkown_id' => $lawyerkown_id, 'details' => $details, 'instructions' => $instructions)); */
                $this->baoSuccess('添加成功', U('lawyerkown/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
  
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('法识分类不能为空');
        }
   
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('法识名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('图片格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($lawyerkown_id = 0) {
        if ($lawyerkown_id = (int) $lawyerkown_id) {
            $obj = D('Lawyerkown');
            if (!$detail = $obj->find($lawyerkown_id)) {
                $this->baoError('请选择要编辑的法识');
            }
          

            if ($this->isPost()) {
                $data = $this->editCheck();
                $details = $this->_post('details', 'SecurityEditorHtml');
                if (empty($details)) {
                    $this->baoError('法识详情不能为空');
                }
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('详细内容含有敏感词：' . $words);
                }
               $data['details']=$details;
              
               
              
                if (false !== $obj->save($data)) {
                  
                    $this->baoSuccess('操作成功', U('lawyerkown/index'));
                }
                $this->baoError('操作失败');
            } else {
                
                $this->assign('detail', $obj->_format($detail));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的法识');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
       
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('法识分类不能为空');
        }
      
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('法识名称不能为空');
        }
        $data['intro'] = htmlspecialchars($data['intro']);
       
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传图片');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('图片格式不正确');
        }
        $data['is_hot'] = (int) $data['is_hot'];
        $data['is_new'] = (int) $data['is_new'];
        $data['is_chose'] = (int) $data['is_chose'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($lawyerkown_id = 0) {
        if (is_numeric($lawyerkown_id) && ($lawyerkown_id = (int) $lawyerkown_id)) {
            $obj = D('Lawyerkown');
            $obj->save(array('lawyerkown_id' => $lawyerkown_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('lawyerkown/index'));
        } else {
            $lawyerkown_id = $this->_post('lawyerkown_id', false);
            if (is_array($lawyerkown_id)) {
                $obj = D('Lawyerkown');
                foreach ($lawyerkown_id as $id) {
                    $obj->save(array('lawyerkown_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('lawyerkown/index'));
            }
            $this->baoError('请选择要删除的法识');
        }
    }

    public function audit($lawyerkown_id = 0) {
        if (is_numeric($lawyerkown_id) && ($lawyerkown_id = (int) $lawyerkown_id)) {
            $obj = D('Lawyerkown');
            $obj->save(array('lawyerkown_id' => $lawyerkown_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('lawyerkown/index'));
        } else {
            $lawyerkown_id = $this->_post('lawyerkown_id', false);
            if (is_array($lawyerkown_id)) {
                $obj = D('Lawyerkown');
                foreach ($lawyerkown_id as $id) {
                    $obj->save(array('lawyerkown_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('lawyerkown/index'));
            }
            $this->baoError('请选择要审核的法识');
        }
    }

    public function cancel($lawyerkown_id = 0) {
        if (is_numeric($lawyerkown_id) && ($lawyerkown_id = (int) $lawyerkown_id)) {
            $obj = D('Lawyerkown');
            $obj->save(array('lawyerkown_id' => $lawyerkown_id, 'is_seckill' => 0));
            $this->baoSuccess('秒杀活动取消成功！', U('lawyerkown/index'));
        } else {
            $lawyerkown_id = $this->_post('lawyerkown_id', false);
            if (is_array($lawyerkown_id)) {
                $obj = D('Lawyerkown');
                foreach ($lawyerkown_id as $id) {
                    $obj->save(array('lawyerkown_id' => $id, 'audit' => 0));
                }
                $this->baoSuccess('秒杀活动取消成功！', U('lawyerkown/index'));
            }
            $this->baoError('请选择要取消秒杀的法识');
        }
    }

}
