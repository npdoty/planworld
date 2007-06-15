require File.dirname(__FILE__) + '/../test_helper'

module PlanwatchEntrySpecHelper
  def create_planwatch_entry(options = {})
    PlanwatchEntry.create(options.reverse_merge({}))
  end
end

context "A PlanwatchEntry (in general)" do
  fixtures :planwatch_entries

  setup do
    @planwatch_entry = planwatch_entries(:first)
  end
  
  specify "should belong to a User" do
    PlanwatchEntry.should.have_association :user
  end

  specify "should belong to a watched User" do
    PlanwatchEntry.should.have_association :watched_user
  end
  
  specify "should belong to a PlanwatchGroup" do
    PlanwatchEntry.should.have_association :group
  end

  specify "should belong have a last viewed date" do
    @planwatch_entry.last_viewed_at.should.not.be.nil
  end
end

context "A new PlanwatchEntry" do
  include PlanwatchEntrySpecHelper
  fixtures :planwatch_entries, :planwatch_groups
  
  specify "should be created" do
    create_planwatch_entry.should.validate
  end
  
  specify "should default to being in group 1" do
    create_planwatch_entry.group.should == planwatch_groups(:first)
  end
end
