<?php

/////////////////////////////////////////////////////////////////////////////
// 这个文件是 美容整形社区项目的一部分
//
// Copyright (c) 2015 – 2020  QQ:1149100178
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者联系qq:easyWe(2504585798)获得详细信息。

class NewscateAction extends CommonAction {

    private $create_fields = array('cate_name', 'orderby');
    private $edit_fields = array('cate_name', 'orderby');

    public function index() {
        $Newscate = D('Newscate');
        $list = $Newscate->fetchAll();
       // dump($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create($parent_id=0) {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Newscate');
            $data['parent_id'] = $parent_id;
            if ($obj->add($data)) {
                $obj->cleanCache();
                $this->baoSuccess('添加成功', U('newscate/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id',$parent_id);
            $this->display();
        }
    }
    
    
    public function child($parent_id=0){
        $datas = D('Newscate')->fetchAll();
        $str = '';

        foreach($datas as $var){
            if($var['parent_id'] == 0 && $var['cate_id'] == $parent_id){
         
                foreach($datas as $var2){

                    if($var2['parent_id'] == $var['cate_id']){
                        $str.='<option value="'.$var2['cate_id'].'">'.$var2['cate_name'].'</option>'."\n\r";
           
                        foreach($datas as $var3){
                            if($var3['parent_id'] == $var2['cate_id']){
                                
                               $str.='<option value="'.$var3['cate_id'].'">&nbsp;&nbsp;--'.$var3['cate_name'].'</option>'."\n\r"; 
                                
                            }
                            
                        }
                    }  
                }
                             
              
            }           
        }
        echo $str;die;
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function edit($cate_id = 0) {
        if ($cate_id = (int) $cate_id) {
            $obj = D('Newscate');
            if (!$detail = $obj->find($cate_id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['cate_id'] = $cate_id;
                if (false !== $obj->save($data)) {
                    $obj->cleanCache();
                    $this->baoSuccess('操作成功', U('newscate/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的商家分类');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['cate_name'] = htmlspecialchars($data['cate_name']);
        if (empty($data['cate_name'])) {
            $this->baoError('分类不能为空');
        }
        $data['orderby'] = (int) $data['orderby'];
        return $data;
    }

    public function delete($cate_id = 0) {
        if (is_numeric($cate_id) && ($cate_id = (int) $cate_id)) {
            $obj = D('Newscate');
            $obj->delete($cate_id);
            $obj->cleanCache();
            $this->baoSuccess('删除成功！', U('newscate/index'));
        } else {
            $cate_id = $this->_post('cate_id', false);
            if (is_array($cate_id)) {
                $obj = D('Newscate');
                foreach ($cate_id as $id) {
                    $obj->delete($id);
                }
                $obj->cleanCache();
                $this->baoSuccess('删除成功！', U('newscate/index'));
            }
            $this->baoError('请选择要删除的商家分类');
        }
    }
    
    public function update() {
        $orderby = $this->_post('orderby', false);
        $obj = D('Newscate');
        foreach ($orderby as $key => $val) {
            $data = array(
                'cate_id' => (int) $key,
                'orderby' => (int) $val
            );
            $obj->save($data);
        }
        $obj->cleanCache();
        $this->baoSuccess('更新成功', U('newscate/index'));
    }

}
