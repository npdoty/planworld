require File.dirname(__FILE__) + '/../test_helper'

module UserSpecHelper
  def create_user(options = {})
    User.create(options.reverse_merge({}))
  end
end

context "A User (in general)" do
  fixtures :users
  
  setup do
    @user = users(:first)
  end
  
  specify "should have a username" do
    @user.username.should.not.be.nil
  end

  specify "should have snitch views" do
    @user.snitch_views.should.not.be.nil
  end

  specify "should have a public archive size" do
    @user.archive_size_pub.should.not.be.nil
  end
  
  xspecify "should have a public archive association" do
    User.should.have_association :public_archive
  end
  
  specify "should have an archive size" do
    @user.archive_size.should.not.be.nil
  end

  xspecify "should have an archive association" do
    User.should.have_association :archive
  end
  
  specify "should have a count of views" do
    @user.views.should.not.be.nil
  end
  
  specify "should have a watch order" do
    @user.watch_order.should.not.be.nil
  end

  specify "should have a theme id" do
    @user.theme_id.should.not.be.nil
  end
  
  xspecify "should belong to a Theme" do
    User.should.have_association :theme
  end
  
  specify "should have a world viewable flag" do
    @user.world_viewable?.should.not.be.nil
  end
  
  specify "should have a snitch enabled flag" do
    @user.snitch_enabled?.should.not.be.nil
  end
  
  specify "should have a date when snitch was activated" do
    @user.snitch_activated_at.should.not.be.nil
  end

  specify "should have default archive settings" do
    @user.default_archive_settings.should.not.be.nil
  end

  specify "should have a last login date" do
    @user.last_logged_in_at.should.not.be.nil
  end
  
  specify "should have a Plan" do
    User.should.have_association :plan
  end
  
  specify "should have a planwatch (list of PlanwatchGroups)" do
    User.should.have_association :planwatch
  end
end

context "A new User" do
  include UserSpecHelper
  fixtures :users
  
  specify "should be created" do
    create_user.should.validate
  end
end
