<?php

/////////////////////////////////////////////////////////////////////////////
// 这个文件是 ×××× 项目的一部分
//
// Copyright (c) 2015 – 2020  QQ:1149100178
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者联系qq:easyWe(2504585798)获得详细信息。

class ActivityAction extends CommonAction {

    private $create_fields = array('cate_id', 'hospital_id','item_id','city_id', 'area_id','business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');
    private $edit_fields = array('cate_id', 'hospital_id','item_id','city_id', 'area_id','business_id', 'title', 'intro', 'photo', 'thumb', 'details', 'price', 'bg_date', 'end_date', 'time', 'sign_end', 'addr', 'orderby', 'sign_num');

    public function index() {
        $Activity = D('Activity');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed' => 0);
        $keyword = $this->_param('keyword', 'htmlspecialchars');
        if ($keyword) {
            $map['title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('title', $title);
        }

        if ($hospital_id = (int) $this->_param('hospital_id')) {
            $map['hospital_id'] = $hospital_id;
            $hospital = D('Hospital')->find($hospital_id);
            $this->assign('hospital_name', $hospital['hospital_name']);
            $this->assign('hospital_id', $hospital_id);
        }

        if ($cate_id = (int) $this->_param('cate_id')) {
            $map['cate_id'] = $cate_id;
            $this->assign('cate_id', $cate_id);
        }
        $count = $Activity->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Activity->where($map)->order(array('activity_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $hospital_ids = array();
        foreach ($list as $key => $val) {
            $hospital_ids[$val['hospital_id']] = $val['hospital_id'];
        }
        $this->assign('cates', D('Activitycate')->fetchAll());
        $this->assign('hospitals', D('Hospital')->itemsByIds($hospital_ids));
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function item() {
        if (IS_AJAX) {
            $hospital_id = $_POST['hospital_id'];
            $list = D('Item')->where(array('hospital_id' => $hospital_id))->order('item_id asc')->select();
            $this->ajaxReturn(array('list' => $list));
        }
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Activity');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('activity/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('cates', D('Activitycate')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型ID不能为空');
        }
        $data['hospital_id'] = (int) $data['hospital_id'];
        $data['item_id'] = (int) $data['item_id'];
        $hospital = D('Hospital')->find($data['hospital_id']);
        $data['city_id'] = $hospital['city_id'];
        $data['area_id'] = $hospital['area_id'];
        $data['business_id'] = $hospital['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        } $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('活动简介不能为空');
        }
        $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的图片');
        }


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


        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        } $data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        } $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('活动开始时间不能为空');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('活动结束时间不能为空');
        }$data['sign_end'] = htmlspecialchars($data['sign_end']);
        if (empty($data['sign_end'])) {
            $this->baoError('报名截止时间不能为空');
        }$data['time'] = htmlspecialchars($data['time']);
        if (empty($data['time'])) {
            $this->baoError('活动具体时间不能为空');
        } $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('活动地址不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('活动标题含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('活动简介含有敏感词：' . $words);
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['create_time'] = NOW_TIME;
        $data['sign_num'] = 0;
        $data['create_ip'] = get_client_ip();
        return $data;
    }

    public function edit($activity_id = 0) {
        if ($activity_id = (int) $activity_id) {
            $obj = D('Activity');
            if (!$detail = $obj->find($activity_id)) {
                $this->baoError('请选择要编辑的活动');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['activity_id'] = $activity_id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('activity/index'));
                }
                $this->baoError('操作失败');
            } else {
                $thumb = unserialize($detail['thumb']);
                $item = D('Item')->where(array('hospital_id'=>$detail['hospital_id']))->select();
                $this->assign('item',$item);
                $this->assign('thumb', $thumb);
                $this->assign('users', D('Users')->find($detail['user_id']));
                $this->assign('cates', D('Activitycate')->fetchAll());
                $this->assign('hospitals', D('Hospital')->find($detail['hospital_id']));
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的活动');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_id'] = (int) $data['cate_id'];
        if (empty($data['cate_id'])) {
            $this->baoError('类型ID不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        $data['hospital_id'] = (int) $data['hospital_id'];
        $data['item_id'] = (int) $data['item_id'];
        $hospital = D('Hospital')->find($data['hospital_id']);
        $data['city_id'] = $hospital['city_id'];
        $data['area_id'] = $hospital['area_id'];
        $data['business_id'] = $hospital['business_id'];
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('活动标题不能为空');
        } $data['intro'] = htmlspecialchars($data['intro']);
        if (empty($data['intro'])) {
            $this->baoError('活动简介不能为空');
        } $data['photo'] = htmlspecialchars($data['photo']);
        if (!isImage($data['photo'])) {
            $this->baoError('请上传正确的图片');
        }

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


        $data['details'] = SecurityEditorHtml($data['details']);
        if (empty($data['details'])) {
            $this->baoError('活动内容不能为空');
        } $data['price'] = htmlspecialchars($data['price']);
        if (empty($data['price'])) {
            $this->baoError('价格不能为空');
        } $data['bg_date'] = htmlspecialchars($data['bg_date']);
        if (empty($data['bg_date'])) {
            $this->baoError('活动开始时间不能为空');
        } $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->baoError('活动结束时间不能为空');
        }$data['sign_end'] = htmlspecialchars($data['sign_end']);
        if (empty($data['sign_end'])) {
            $this->baoError('报名截止时间不能为空');
        }$data['time'] = htmlspecialchars($data['time']);
        if (empty($data['time'])) {
            $this->baoError('活动具体时间不能为空');
        } $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->baoError('活动地址不能为空');
        }
        if ($words = D('Sensitive')->checkWords($data['details'])) {
            $this->baoError('活动内容含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['title'])) {
            $this->baoError('活动标题含有敏感词：' . $words);
        }
        if ($words = D('Sensitive')->checkWords($data['intro'])) {
            $this->baoError('活动简介含有敏感词：' . $words);
        }
        return $data;
    }

    public function delete($activity_id = 0) {
        if (is_numeric($activity_id) && ($activity_id = (int) $activity_id)) {
            $obj = D('Activity');
            $obj->delete($activity_id);
            $this->baoSuccess('删除成功！', U('activity/index'));
        } else {
            $activity_id = $this->_post('activity_id', false);
            if (is_array($activity_id)) {
                $obj = D('Activity');
                foreach ($activity_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('activity/index'));
            }
            $this->baoError('请选择要删除的活动');
        }
    }

    public function audit($activity_id = 0) {
        if (is_numeric($activity_id) && ($activity_id = (int) $activity_id)) {
            $obj = D('Activity');
            $obj->save(array('activity_id' => $activity_id, 'audit' => 1));
            $this->baoSuccess('审核成功！', U('activity/index'));
        } else {
            $activity_id = $this->_post('activity_id', false);
            if (is_array($activity_id)) {
                $obj = D('Activity');
                foreach ($activity_id as $id) {
                    $obj->save(array('activity_id' => $id, 'audit' => 1));
                }
                $this->baoSuccess('审核成功！', U('activity/index'));
            }
            $this->baoError('请选择要审核的活动');
        }
    }

}
