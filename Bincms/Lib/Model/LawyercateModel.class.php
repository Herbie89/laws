<?php

/////////////////////////////////////////////////////////////////////////////
// 这个文件是 ×××× 项目的一部分
//
// Copyright (c) 2015 – 2020  QQ:1149100178
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者联系qq:easyWe(2504585798)获得详细信

class LawyercateModel extends CommonModel {

    protected $pk = 'cate_id';
    protected $tableName = 'Lawyer_cate';
    protected $token = 'Lawyer_cate';
    protected $orderby = array('orderby' => 'asc');

    public function getParentsId($id) {
        $data = $this->fetchAll();
        $parent_id = $data[$id]['parent_id']; 
        return $parent_id;
    }

    public function getChildren($id) {
        $local = array();
        //暂时 只支持 2级分类
        $data = $this->fetchAll();
        $local[] = $id;
        foreach ($data as $val) {
            if ($val['parent_id'] == $id) {
                $local[] = $val['cate_id'];
            }
        }
        return $local;
    }

}