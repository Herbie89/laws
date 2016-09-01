<?php

/////////////////////////////////////////////////////////////////////////////
	// 这个文件是 美容整形社区咨询的一部分
	//
	// Copyright (c) 2015 – 2020  QQ:1149100178
	// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
	// 或者联系qq:easyWe(2504585798)获得详细信息。

class ItemAction extends CommonAction { //按逻辑  instructions  和  details 要分表出去

    private $create_fields = array('user_id', 'orderby', 'cate_id',  'title', 'item_times','photo', 'thumb', 'is_hot', 'is_new', 'is_chose', 'details','is_top');
    private $edit_fields = array('user_id', 'orderby',  'cate_id',  'title', 'photo', 'thumb',  'item_times','is_hot', 'is_new', 'is_chose','details','is_top');

    public function _initialize() {
        parent::_initialize();
        $this->Itemcates = D('Itemcate')->fetchAll();
        $this->assign('cates', $this->Itemcates);
    }

    public function index() {
        $Item = D('Item');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Itemcate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        if ($user_id = (int) $this->_param('user_id')) {
            $map['user_id'] = $user_id;
            $hospital = D('Users')->find($user_id);
            $this->assign('uname', $hospital['account']);
            $this->assign('user_id', $user_id);
        }
        if ($audit = (int) $this->_param('audit')) {
            $map['audit'] = ($audit === 1 ? 1 : 0);
            $this->assign('audit', $audit);
        }
        $count = $Item->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Item->where($map)->order(array('item_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $user_ids[$val['user_id']] = $val['user_id'];
            }
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val = $Item->_format($val);
            $list[$k] = $val;
        }
      if ($user_ids) {
            $this->assign('users', D('Users')->itemsByIds($user_ids));
        } 
        $this->assign('cates', D('Itemcate')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

   

        public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Item');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if (empty($details)) {
                $this->baoError('咨询详情不能为空');
            }
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('详细内容含有敏感词：' . $words);
            }
            $data['details']= $details;
         
            $thumb = $this->_param('thumb', false);
            foreach ($thumb as $k => $val) {
                if (empty($val)) {
                    unset($thumb[$k]);
                }
                if (!isImage($val)) {
                    unset($thumb[$k]);
                }
            }
            $data['thumb'] = serialize($thumb);
            if ($item_id = $obj->add($data)) {
                //$wei_pic = D('Weixin')->getCode($item_id, 2); //咨询类型是2
               // $obj->save(array('item_id' => $item_id, 'wei_pic' => $wei_pic));
              //  D('Itemdetails')->add(array('item_id' => $item_id, 'details' => $details, 'instructions' => $instructions));
                $this->baoSuccess('添加成功', U('item/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('会员不能为空');
        }
        $hospital = D('Users')->find($data['user_id']);
        if (empty($hospital)) {
            $this->baoError('请选择正确的会员');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('咨询分类不能为空');
        }
       /*  $data['lng'] = $hospital['lng'];
        $data['lat'] = $hospital['lat'];
        $data['city_id'] = $hospital['city_id'];
        $data['area_id'] = $hospital['area_id'];
        $data['business_id'] = $hospital['business_id']; */

        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('咨询名称不能为空');
        }
      /*   $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('副标题不能为空');
        } */
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
        $data['is_top'] = (int) $data['is_top'];
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($item_id = 0) {
        if ($item_id = (int) $item_id) {
            $obj = D('Item');
            if (!$detail = $obj->find($item_id)) {
                $this->baoError('请选择要编辑的咨询');
            }
           // $item_details = D('Itemdetails')->getDetail($item_id);

            if ($this->isPost()) {
                $data = $this->editCheck();
                $details = $this->_post('details', 'SecurityEditorHtml');
                if (empty($details)) {
                    $this->baoError('咨询详情不能为空');
                }
              
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('详细内容含有敏感词：' . $words);
                }
                $data['details']=$details;
                $thumb = $this->_param('thumb', false);
                foreach ($thumb as $k => $val) {
                    if (empty($val)) {
                        unset($thumb[$k]);
                    }
                    if (!isImage($val)) {
                        unset($thumb[$k]);
                    }
                }
                $data['thumb'] = serialize($thumb);

                $data['item_id'] = $item_id;
                /* if (!empty($detail['wei_pic'])) {
                    if (true !== strpos($detail['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($item_id, 2);
                        $data['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($item_id, 2);
                    $data['wei_pic'] = $wei_pic;
                }
                $ex = array(
                    'item_id' => $item_id,
                    'details' => $details,
                    'instructions' => $instructions,
                ); */
                if (false !== $obj->save($data)) {
                    //D('Itemdetails')->save($ex);
                    $this->baoSuccess('操作成功', U('item/index'));
                }
                $this->baoError('操作失败');
            } else {
                 $this->assign('detail', $obj->_format($detail));
                $thumb = unserialize($detail['thumb']);
                $this->assign('thumb', $thumb);
                $this->assign('Users', D('Users')->find($detail['user_id']));
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的咨询');
        }
    }
    public function reply (){
    	
    	
    	
    	$map = array('closed' => 0);

    	
    	$item_id =(int) $this->_param('item_id');
    	$items=D('Item')->where(array('item_id'=>$item_id))->find();
    		
    	$this->display();
    		
    
    	
    	}
    	
    	
    	
    	
    
    
    public function select(){
    	$Reply = D('Reply');
    	import('ORG.Util.Page'); // 导入分页类
    	
    	
    	$map = array('closed' => 0);
    	if($item_id =(int) $this->_param('item_id')){
    		$map['item_id'] = $item_id;
    		
    	}
//     	if($nickname = $this->_param('nickname','htmlspecialchars')){
//     		$map['nickname'] = array('LIKE','%'.$nickname.'%');
//     		$this->assign('nickname',$nickname);
//     	}
//     	if($ext0 = $this->_param('ext0','htmlspecialchars')){
//     		$map['ext0'] = array('LIKE','%'.$ext0.'%');
//     		$this->assign('ext0',$ext0);
//     	}
    	$count = $Reply->where($map)->count(); // 查询满足要求的总记录数
    	$Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
    	$pager = $Page->show(); // 分页显示输出
    	$list = $Reply->where($map)->order(array('reply_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
     foreach ($list as $k => $val) {
            if ($val['lawyer_id']) {
                $user_ids[$val['lawyer_id']] = $val['lawyer_id'];
            }
           
            $list[$k] = $val;
        }
      if ($user_ids) {
            $this->assign('lawyers', D('Lawyer')->itemsByIds($user_ids));
        } 
    	
    	$this->assign('list', $list); // 赋值数据集
    	$this->assign('page', $pager); // 赋值分页输出
    	$this->display(); // 输出模板
    
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
       
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('咨询分类不能为空');
        }
        $data['lng'] = $hospital['lng'];
        $data['lat'] = $hospital['lat'];
        $data['city_id'] = $hospital['city_id'];
        $data['area_id'] = $hospital['area_id'];
        $data['business_id'] = $hospital['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('咨询名称不能为空');
        }
        
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

        $data['is_top'] = (int) $data['is_top'];
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($item_id = 0) {
        if (is_numeric($item_id) && ($item_id = (int) $item_id)) {
            $obj = D('Item');
            $obj->save(array('item_id' => $item_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('item/index'));
        } else {
            $item_id = $this->_post('item_id', false);
            if (is_array($item_id)) {
                $obj = D('Item');
                foreach ($item_id as $id) {
                    $obj->save(array('item_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('item/index'));
            }
            $this->baoError('请选择要删除的咨询');
        }
    }

    public function deleter($item_id = 0) {
    	if (is_numeric($item_id) && ($item_id = (int) $item_id)) {
    		$obj = D('Reply');
    		$obj->save(array('reply_id' => $item_id, 'closed' => 1));
    		$this->baoSuccess('删除成功！');
    	} else {
    		$item_id = $this->_post('item_id', false);
    		if (is_array($item_id)) {
    			$obj = D('Item');
    			foreach ($item_id as $id) {
    				$obj->save(array('reply_id' => $id, 'closed' => 1));
    			}
    			$this->baoSuccess('删除成功！');
    		}
    		$this->baoError('请选择要删除的咨询回复');
    	}
    }
    
    
    
    
    public function audit($item_id = 0) {
        if (is_numeric($item_id) && ($item_id = (int) $item_id)) {
            $obj = D('Item');
            $obj->save(array('item_id' => $item_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('item/index'));
        } else {
            $item_id = $this->_post('item_id', false);
            if (is_array($item_id)) {
                $obj = D('Item');
                foreach ($item_id as $id) {
                    $obj->save(array('item_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('item/index'));
            }
            $this->baoError('请选择要审核的咨询');
        }
    }

    public function cancel($item_id = 0) {
        if (is_numeric($item_id) && ($item_id = (int) $item_id)) {
            $obj = D('Item');
            $obj->save(array('item_id' => $item_id, 'is_seckill' => 0));
            $this->baoSuccess('秒杀活动取消成功！', U('item/index'));
        } else {
            $item_id = $this->_post('item_id', false);
            if (is_array($item_id)) {
                $obj = D('Item');
                foreach ($item_id as $id) {
                    $obj->save(array('item_id' => $id, 'audit' => 0));
                }
                $this->baoSuccess('秒杀活动取消成功！', U('item/index'));
            }
            $this->baoError('请选择要取消秒杀的咨询');
        }
    }

}
