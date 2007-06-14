require File.dirname(__FILE__) + '/helper'

context "A Time object extended with TimeExtensions" do
  specify "should return a range of dates when sent #week" do
    week = Time.now.week

    week.should.be.an.instance_of Range
    week.should === Time.now
  end

  specify "should return this week when sent #this_week" do
    this_week = Time.this_week

    this_week.should.be.an.instance_of Range
    this_week.should === Time.now
  end
end
