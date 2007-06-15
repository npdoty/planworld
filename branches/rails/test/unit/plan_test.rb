require File.dirname(__FILE__) + '/../test_helper'

module PlanSpecHelper
  def create_plan(options = {})
    Plan.create(options.reverse_merge({}))
  end
end

context "A Plan (in general)" do
  fixtures :plans
  
  setup do
    @plan = plans(:first)
  end
  
  specify "should have a body" do
    @plan.body.should.not.be.nil
  end
  
  specify "should belong to a User" do
    Plan.should.have_association :user
  end
end

context "A new Plan" do
  include PlanSpecHelper
  fixtures :plans
  
  specify "should be created" do
    create_plan.should.validate
  end
end
