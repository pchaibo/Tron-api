<?php
/**
 * Created by PhpStorm.
 * User: gaoxi
 * Date: 2017-08-13
 * Time: 14:02
 */
namespace Admin\Controller;

class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    //列表
    public function index()
    {
        $admin_auth_group_model = D('AdminAuthGroup');
        $data = $admin_auth_group_model->getGroupList();
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }

    /**
     * 添加角色页面显示
     */
    public function addGroup()
    {
        if(IS_POST){
            $is_manager = I('post.is_manager') == 'on' ? 1 :0;
            $params = array(
                'title' => I('title','','trim'),
                'is_manager'=>$is_manager,
                'status' => 1,
                'rules' => '',
            );

            if(!$params['title']){
               $this->ajaxReturn(['status'=>0,'msg'=>'请输入角色名称!']);
            }
            $admin_auth_group_model = D('AdminAuthGroup');
            $add_group_result = $admin_auth_group_model->add($params);
            $this->ajaxReturn(['status'=>$add_group_result]);
        }else{
            $this->display();
        }

    }


    /**
     * 编辑角色页面显示
     */
    public function editGroup()
    {
        if(IS_POST){
            $params = array(
                'id' => I('id', 0, 'intval'),
                'title' => I('title'),
            );
            if(!$params['id']){
                $this->ajaxReturn(['status'=>'error','msg'=>'角色不存在!']);
            }
            if(!$params['title']){
                parent::ajaxError('请输入角色名称!');
            }
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $save_group_result = $admin_auth_group_model->save($params);
            $this->ajaxReturn(['status'=>$save_group_result,'msg'=>'修改成功!']);
        }else{
            $id = I('id', 0, 'intval');
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $group_info = $admin_auth_group_model->findGroup($id);

            $this->assign('group_info', $group_info);
            $this->display();
        }

    }

    /**
     * 删除角色处理
     */
    public function deleteGroup()
    {
        $id = I('id', 0, 'intval');
        if(!$id){
            parent::ajaxError('角色不存在!');
        }
        /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
        $admin_auth_group_model = D('AdminAuthGroup');
        $group_info = $admin_auth_group_model->findGroup($id);
        if(!$group_info){
            $this->ajaxReturn(['status'=>0,'msg'=>'角色不存在!']);
        }
        $change_result = $admin_auth_group_model->changeResult($id, 2);
        $this->ajaxReturn(['status'=>$change_result]);
    }

    /**
     * 分配角色
     */
    public function giveRole()
    {
        if(IS_POST){
            $user_id = I('user_id', 0, 'intval');
            if(!$user_id){
                parent::ajaxError('用户不存在!');
            }

            $group_id = $_POST['group_id'];
            //html_entity_decode($string)
            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_access_model = D('AdminAuthGroupAccess');

            if(!empty($group_id)){
                //删除原有角色
                $admin_auth_group_access_model->where(array('uid'=>$user_id))->delete();
                foreach($group_id as $v){
                    $add_data = array(
                        'uid' => $user_id,
                        'group_id' => $v,
                    );
                    $admin_auth_group_access_model->add($add_data);
                }
            }
            parent::ajaxSuccess('分配成功!');
        }else{
            $user_id = I('user_id', 0, 'intval');

            /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
            $admin_auth_group_model = D('AdminAuthGroup');
            $data = $admin_auth_group_model->getGroupList($user_id);

            $this->assign('list', $data['list']);
            $this->assign('user_id', $user_id);
            $this->display();
        }
    }

    /**
     * 分配权限
     */
    public function ruleGroup()
    {
        /* @var $admin_auth_group_model \Admin\Model\AdminAuthGroupModel */
        $admin_auth_group_model = D('AdminAuthGroup');
        if(IS_POST){
            $data = I('post.');
            $rule_ids = implode(",", $data['menu']);
            $role_id = $data['roleid'];
            if(!count($rule_ids)){
                $this->ajaxReturn(['status'=>0,'msg'=>'请选择需要分配的权限']);
            }
            if($admin_auth_group_model->addAuthRule($rule_ids, $role_id) !== false){
                $this->ajaxReturn(['status'=>1,'msg'=>'分配成功']);
            }else{
                $this->ajaxReturn(['status'=>0,'msg'=>'分配失败，请检查']);
            }
        }else{

            $role_id = I('get.roleid',0,'intval');
            /* @var $menu_model \Admin\Model\AdminMenuModel */
            $menu_model = D('AdminMenu');

            $menus = get_column($menu_model->selectAllMenu(2),2);
            $role_info = $admin_auth_group_model->findGroup($role_id);

            if($role_info['rules']){
                $rulesArr = explode(',',$role_info['rules']);

                $this->assign('rulesArr',$rulesArr);
            }
            $this->assign('menus',$menus);
            $this->assign('role_id',$role_id);
            $this->display();
        }
    }

}