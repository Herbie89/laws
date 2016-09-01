<?php

/////////////////////////////////////////////////////////////////////////////
	// 这个文件是 美容整形社区项目的一部分
	//
	// Copyright (c) 2015 – 2020  QQ:1149100178
	// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
	// 或者联系qq:easyWe(2504585798)获得详细信息。
class LawyerAction extends CommonAction {

    private $create_fields = array('cate_id','province_id','city_id','town_id','laccount','lpassword','lawyer_name','reg_ip','face','qq','intro', 'details','logo','email','is_zz','is_lvxie','istop','closed', 'mobile','nickname', 'professnumber','reg_time','photo', 'addr', 'tel','contact', 'tags', 'orderby');
    private $edit_fields =array('cate_id','province_id','city_id','town_id','laccount','lpassword','lawyer_name','reg_ip','face','qq','intro', 'details','logo','email','is_zz','is_lvxie','istop','closed', 'mobile','nickname', 'professnumber','reg_time','photo', 'addr', 'tel','contact', 'tags', 'orderby'); 

    public function index() {
        $Lawyer = D('Lawyer');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['lawyer_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
       /*  if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        } */
       /*  if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        } */
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Lawyer')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }

        $count = $Lawyer->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Lawyer->order(array('lawyer_id' => 'desc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {
            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
			 if ($val['country_id']) {
                $list[$k]['country_name'] =  D('Chinaarea')->field('region_name')->where(array('region_id'=>$val['country_id']))->find();
				
				$list[$k]['city_name'] = D('Chinaarea')->field('region_name')->where(array('region_id'=>$val['city_id']))->find();
				
            }
			
			
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
       // $this->assign('citys', D('City')->fetchAll());
       // $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Lawyercate')->fetchAll());
      //  $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function apply() {
        $Lawyer = D('Lawyer');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'audit' => 0);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['lawyer_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
       /*  if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        } */
        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Lawyercate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }

        $count = $Lawyer->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Lawyer->order(array('lawyer_id' => 'asc'))->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
		//$areas=array();
        foreach ($list as $k => $val) {

            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
				
				
				
				
				
            }
			
			 if ($val['country_id']) {
                $list[$k]['country_name'] =  D('Chinaarea')->field('region_name')->where(array('region_id'=>$val['country_id']))->find();
				
				$list[$k]['city_name'] = D('Chinaarea')->field('region_name')->where(array('region_id'=>$val['city_id']))->find();
				
            }
			
			
        }
		//print_r($list);
        $this->assign('users', D('Users')->itemsByIds($ids));
		//$this->assign('area',D('Chinaarea')->itemsByIds($areas));
		//$this->assign('citym',$v=D('Chinaarea')->getChildren($areas));
		 //print_r($v);
       // $this->assign('citys', D('City')->fetchAll());
       // $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Lawyercate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data2=$data = $this->createCheck();
            $obj = D('Lawyer');
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('律师介绍含有敏感词：' . $words);
            }
            
            if ($lawyer_id = $obj->add($data)) {
               // D('Lawyerdetails')->upDetails($lawyer_id, $ex);
                $this->baoSuccess('添加成功', U('lawyer/apply'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Lawyercate')->fetchAll());
          //  $this->assign('province', $v=D('Chinaarea')->where(array('parent_id'=>1))->select());
          // dump($v);
            $this->display();
        }
    }

    public function select() {
        $Lawyer = D('Lawyer');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0, 'audit' => 1);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['lawyer_name|tel'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        if ($city_id = (int) $this->_param('city_id')) {
            $map['city_id'] = $city_id;
            $this->assign('city_id', $city_id);
        }
        if ($area_id = (int) $this->_param('area_id')) {
            $map['area_id'] = $area_id;
            $this->assign('area_id', $area_id);
        }

        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = array('IN', D('Lawyercate')->getChildren($cate_id));
            $this->assign('cate_id', $cate_id);
        }
        $count = $Lawyer->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Lawyer->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $k => $val) {

            if ($val['user_id']) {
                $ids[$val['user_id']] = $val['user_id'];
            }
        }
        $this->assign('users', D('Users')->itemsByIds($ids));
        $this->assign('citys', D('City')->fetchAll());
        $this->assign('areas', D('Area')->fetchAll());
        $this->assign('cates', D('Lawyercate')->fetchAll());
        $this->assign('business', D('Business')->fetchAll());
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
       /*  $data['lawyer_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $lawyer = D('Lawyer')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($lawyer)) {
            $this->baoError('该管理者已经拥有律师了');
        } */

        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('所属分类不能为空');
        } 
        $data['province_id'] = (int) $data['province_id'];
        $data['city_id'] = (int) $data['city_id'];
        if (empty($data['province_id'])) {
            $this->baoError('所在省市不能为空');
        } $data['city_id'] = (int) $data['city_id'];
        if (empty($data['city_id'])) {
            $this->baoError('所在省市不能为空');
        }
        $data['laccount'] = htmlspecialchars($data['laccount']);
        if (empty($data['laccount'])) {
        	$this->baoError('律师注册账号不能为空');
        }
        $data['lpassword'] = htmlspecialchars($data['lpassword']);
        if (empty($data['lpassword'])) {
        	$this->baoError('律师密码不能为空');
        }
        $data['lrepassword'] = htmlspecialchars($data['lrepassword']);
        if (empty($data['lrepassword'])) {
        	$this->baoError('律师确认密码不能为空');
        }
        
        if ($data['lrepassword'] != $data['lpassword']) {
        	$this->baoError('两次密码不一致');
        }
        
         $data['lawyer_name'] = htmlspecialchars($data['lawyer_name']);
        if (empty($data['lawyer_name'])) {
            $this->baoError('真实不能为空');
        } 
        $data['face'] = htmlspecialchars($data['face']);
        if (empty($data['face'])) {
            $this->baoError('请上传律师face');
        }
        if (!isImage($data['face'])) {
            $this->baoError('律师face格式不正确');
        } 
        $data['photo'] = htmlspecialchars($data['photo']);
        
        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('律师地址不能为空');
        }
        $data['tel'] = htmlspecialchars($data['tel']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if ( empty($data['mobile'])) {
            $this->baoError('律师手机不能为空');
        }
        
        $data['professnumber'] = htmlspecialchars($data['professnumber']);
        if ( empty($data['professnumber'])) {
        	$this->baoError('律师资格证不能为空');
        }
 
        $data['contact'] = htmlspecialchars($data['contact']);
        if ( empty($data['professnumber'])) {
        	$this->baoError('应急联系人不能为空');
        }
        
        $data['tags'] = str_replace(',', '，', htmlspecialchars($data['tags']));
       
        if ( empty($data['professnumber'])) {
        	$this->baoError('律师专长不能为空');
        }
        
       
        $data['orderby'] = (int) $data['orderby'];
        $data['price'] = (int) $data['price'];
        $data['is_pei'] = (int) $data['is_pei'];
        $data['qq'] = htmlspecialchars($data['qq']);
        $data['intro'] = htmlspecialchars($data['intro']);
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($lawyer_id = 0) {

        if ($lawyer_id = (int) $lawyer_id) {
            $obj = D('Lawyer');
            if (!$detail = $obj->find($lawyer_id)) {
                $this->baoError('请选择要编辑的律师');
            }
            if ($this->isPost()) {
                $data = $this->editCheck($lawyer_id);
                $data['lawyer_id'] = $lawyer_id;
                $details = $this->_post('details', 'SecurityEditorHtml');
                if ($words = D('Sensitive')->checkWords($details)) {
                    $this->baoError('律师介绍含有敏感词：' . $words);
                }
                $bank = $this->_post('bank', 'htmlspecialchars');
                $lawyerdetails = D('Lawyerdetails')->find($lawyer_id);

                $ex = array(
                    'details' => $details,
                    'bank' => $bank,
                    'near' => $data['near'],
                    'price' => $data['price'],
                    'business_time' => $data['business_time'],
                );
                if (!empty($lawyerdetails['wei_pic'])) {
                    if (true !== strpos($lawyerdetails['wei_pic'], "https://mp.weixin.qq.com/")) {
                        $wei_pic = D('Weixin')->getCode($lawyer_id, 1);
                        $ex['wei_pic'] = $wei_pic;
                    }
                } else {
                    $wei_pic = D('Weixin')->getCode($lawyer_id, 1);
                    $ex['wei_pic'] = $wei_pic;
                }
                unset($data['near'], $data['price'], $data['business_time']);
                if (false !== $obj->save($data)) {
                    D('Lawyerdetails')->upDetails($lawyer_id, $ex);
                    $this->baoSuccess('操作成功', U('lawyer/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('areas', D('Area')->fetchAll());
                $this->assign('cates', D('Lawyercate')->fetchAll());
                $this->assign('business', D('Business')->fetchAll());
                $this->assign('ex', D('Lawyerdetails')->find($lawyer_id));
                $this->assign('user', D('Users')->find($detail['user_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的律师');
        }
    }

    private function editCheck($lawyer_id) {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['user_id'] = (int) $data['user_id'];
        if (empty($data['user_id'])) {
            $this->baoError('管理者不能为空');
        }
        $lawyer = D('Lawyer')->find(array('where' => array('user_id' => $data['user_id'])));
        if (!empty($lawyer) && $lawyer['lawyer_id'] != $lawyer_id) {
            $this->baoError('该管理者已经拥有律师了');
        }
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('分类不能为空');
        } 
        $data['city_id'] = (int) $data['city_id'];
        $data['area_id'] = (int) $data['area_id'];
        if (empty($data['area_id'])) {
            $this->baoError('所在区域不能为空');
        } $data['business_id'] = (int) $data['business_id'];
        if (empty($data['business_id'])) {
            $this->baoError('所在商圈不能为空');
        } $data['lawyer_name'] = htmlspecialchars($data['lawyer_name']);
        if (empty($data['lawyer_name'])) {
            $this->baoError('律师名称不能为空');
        } $data['logo'] = htmlspecialchars($data['logo']);
        if (empty($data['logo'])) {
            $this->baoError('请上传律师LOGO');
        }
        if (!isImage($data['logo'])) {
            $this->baoError('律师LOGO格式不正确');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (empty($data['photo'])) {
            $this->baoError('请上传律师缩略图');
        }
        if (!isImage($data['photo'])) {
            $this->baoError('律师缩略图格式不正确');
        } $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('律师地址不能为空');
        } $data['tel'] = htmlspecialchars($data['tel']);
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['tel']) && empty($data['mobile'])) {
            $this->baoError('律师电话不能为空');
        }

        $data['contact'] = htmlspecialchars($data['contact']);
        $data['tags'] = htmlspecialchars($data['tags']);
        $data['near'] = htmlspecialchars($data['near']);
        $data['business_time'] = htmlspecialchars($data['business_time']);
        $data['orderby'] = (int) $data['orderby'];
        $data['lng'] = htmlspecialchars($data['lng']);
        $data['lat'] = htmlspecialchars($data['lat']);
        $data['price'] = (int) $data['price'];
        $data['is_pei'] = (int) $data['is_pei'];
        return $data;
    }

    public function delete($lawyer_id = 0) {
        if (is_numeric($lawyer_id) && ($lawyer_id = (int) $lawyer_id)) {
            $obj = D('Lawyer');
            $obj->save(array('lawyer_id' => $lawyer_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('lawyer/index'));
        } else {
            $lawyer_id = $this->_post('lawyer_id', false);
            if (is_array($lawyer_id)) {
                $obj = D('Lawyer');
                foreach ($lawyer_id as $id) {
                    $obj->save(array('lawyer_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('lawyer/index'));
            }
            $this->baoError('请选择要删除的律师');
        }
    }
    
    
    public function child($area_id = 0){
    	$datas = D('China_area')->where(array('region_type'=>3))->select();
    	$str ='<option value="0">请选择</option>';
    	foreach($datas as $val){
    		if($val['parent_id'] == $area_id){
    			$str.='<option value="'.$val['region_id'].'">'.$val['region_name'].'</option>';
    		}
    	}
    	echo $str;die;
    }

    public function audit($lawyer_id = 0) {
        if (is_numeric($lawyer_id) && ($lawyer_id = (int) $lawyer_id)) {
            $obj = D('Lawyer');
            $obj->save(array('lawyer_id' => $lawyer_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('lawyer/apply'));
        } else {
            $lawyer_id = $this->_post('lawyer_id', false);
            if (is_array($lawyer_id)) {
                $obj = D('Lawyer');
                foreach ($lawyer_id as $id) {
                    $obj->save(array('lawyer_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('lawyer/apply'));
            }
            $this->baoError('请选择要审核的律师');
        }
    }

    public function login($lawyer_id) {
        $obj = D('Lawyer');
        if (!$detail = $obj->find($lawyer_id)) {
            $this->error('请选择要编辑的律师');
        }
        if (empty($detail['user_id'])) {
            $this->error('该用户没有绑定管理者');
        }
        setUid($detail['user_id']);
        header("Location:" . U('lawyercenter/index/index'));
        die;
    }
    
    
    public function ding($lawyer_id){
        $obj = D('Lawyer');
        if (!$detail = $obj->find($lawyer_id)) {
            $this->error('请选择要编辑的律师');
        }
        $data = array('is_ding'=>0,'lawyer_id'=>$lawyer_id);
        if($detail['is_ding'] == 0){
            $data['is_ding'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功',U('lawyer/index'));
    }
    
    public function pei($lawyer_id){
        $obj = D('Lawyer');
        if (!$detail = $obj->find($lawyer_id)) {
            $this->error('请选择要编辑的律师');
        }
        $data = array('is_pei'=>0,'lawyer_id'=>$lawyer_id);
        if($detail['is_pei'] == 0){
            $data['is_pei'] = 1;
        }
        $obj->save($data);
        $this->baoSuccess('操作成功',U('lawyer/index'));
    }
  

}
