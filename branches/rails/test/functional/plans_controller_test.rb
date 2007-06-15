require File.dirname(__FILE__) + '/../test_helper'
require 'plans_controller'

# Re-raise errors caught by the controller.
class PlansController; def rescue_action(e) raise e end; end

class PlansControllerTest < Test::Unit::TestCase
  fixtures :plans, :users
  
  def setup
    @controller = PlansController.new
    @request    = ActionController::TestRequest.new
    @response   = ActionController::TestResponse.new
  end
  
  def test_should_get_index
    assert_requires_login(:quentin) do |c|
      c.get :index
      assert_response :success
  
      assert assigns(:plans)
    end
  end
  
  def test_should_get_new
    assert_requires_login(:quentin) do |c|
      c.get :new
      assert_response :success
    end
  end
  
  def test_should_create_plan
    assert_requires_login(:quentin) do |c|
      assert_difference "Plan.count" do
        c.post :create, :plan => plan_attributes
      end
    
      assert_response :redirect
      assert_redirected_to :controller => "plans", :action => :show, :id => assigns(:plan).id
    end
  end
  
  def test_should_show_plan
    assert_requires_login(:quentin) do |c|
      c.get :show, :id => plans(:first).id
      assert_response :success
  
      assert_equal plans(:first), assigns(:plan)
    end
  end
  
  def test_should_get_edit
    assert_requires_login(:quentin) do |c|
      c.get :edit, :id => plans(:first).id
      assert_response :success
    
      assert_equal plans(:first), assigns(:plan)
    end
  end
  
  def test_should_update_plan
    assert_requires_login(:quentin) do |c|
      plan = plans(:first)
      c.post :update, :id => plan.id, :plan => plan_attributes(plan)
    
      assert_response :redirect
      assert_redirected_to :controller => "plans", :action => :show, :id => plan.id
    
      plan = plans(:first, true)
      assert_equal plan, assigns(:plan)
      # assert_equal new_value, plan.attribute
    end
  end
  
  def test_should_destroy_plan
    assert_requires_login(:quentin) do |c|
      assert_difference "Plan.count", -1 do
        c.post :destroy, :id => plans(:first).id
      end
    
      assert_response :redirect
      assert_redirected_to :controller => "plans", :action => :index
    end
  end
  
protected

  def plan_attributes(obj = nil, opts = {})
    opts = obj and obj = nil if obj.is_a?(Hash)
    
    if obj
      obj.attributes.merge(opts.stringify_keys)
    else
      opts.reverse_merge({})
    end
  end
end