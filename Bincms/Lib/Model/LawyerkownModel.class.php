<?php

/////////////////////////////////////////////////////////////////////////////
// 这个文件是 美容整形社区项目的一部分
//
// Copyright (c) 2016 – 2020  QQ:1149100178
// 要查看完整的版权信息和许可信息，请查看源代码中附带的 COPYRIGHT 文件，
// 或者联系qq:easyWe(2504585798)获得详细信息。
class LawyerkownModel extends CommonModel {

    protected $pk = 'lawyerkown_id';
    protected $tableName = 'lawyerkown';

    public function _format($data) {
       /*  $data['save'] = round(($data['price'] - $data['lawyerkown_price']) / 100, 2);
        $data['price'] = round($data['price'] / 100, 2);
        $data['lawyerkown_price'] = round($data['lawyerkown_price'] / 100, 2);
        $data['mobile_fan'] = round($data['mobile_fan'] / 100, 2);
        $data['settlement_price'] = round($data['settlement_price'] / 100, 2);
        $data['discount'] = round($data['lawyerkown_price'] * 10 / $data['price'], 1); */
        return $data;
    }

    public function CallDataForMat($lawyerkowns) { //专门针对CALLDATA 标签处理的
        if (empty($lawyerkowns))
            return array();
        $obj = D('Hospital');
        $hospital_ids = array();
        foreach ($lawyerkowns as $k => $val) {
            $hospital_ids[$val['hospital_id']] = $val['hospital_id'];
        }
        $hospitals = $obj->lawyerkownsByIds($hospital_ids);
        foreach ($lawyerkowns as $k => $val) {
            $val['hospital'] = $hospitals[$val['hospital_id']];
            $lawyerkowns[$k] = $val;
        }
        return $lawyerkowns;
    }

}
