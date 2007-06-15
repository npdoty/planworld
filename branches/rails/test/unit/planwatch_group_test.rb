require File.dirname(__FILE__) + '/../test_helper'

module PlanwatchGroupSpecHelper
  def create_planwatch_group(options = {})
    PlanwatchGroup.create(options.reverse_merge(:name => "Sample Group"))
  end
end

context "A PlanwatchGroup (in general)" do
  fixtures :planwatch_groups

  setup do
    @planwatch_group = planwatch_groups(:first)
  end

  specify "should belong to a user" do
    PlanwatchGroup.should.have_association :user
  end

  specify "should have many PlanwatchEntries" do
    PlanwatchGroup.should.have_association :planwatch_entries
  end

  specify "should have many watched Users" do
    PlanwatchGroup.should.have_association :watched_users
  end
  
  specify "should act as a list" do
    @planwatch_group.should.act_as_a_list
  end
end

context "A new PlanwatchGroup" do
  include PlanwatchGroupSpecHelper
  fixtures :planwatch_groups
  
  specify "should be created" do
    create_planwatch_group.should.validate
  end
  
  specify "should require a name" do
    create_planwatch_group(:name => nil).should.not.validate
  end
end
