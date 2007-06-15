# == Schema Information
#
# Table name: planwatch_entries
#
#  id                 :integer(11)   not null, primary key
#  user_id            :integer(11)   
#  watched_user_id    :integer(11)   
#  planwatch_group_id :integer(11)   
#  last_viewed_at     :datetime      
#  updated_at         :datetime      
#  created_at         :datetime      
#

class PlanwatchEntry < ActiveRecord::Base
  belongs_to :group, :class_name => "PlanwatchGroup", :foreign_key => "planwatch_group_id"
  belongs_to :user
  belongs_to :watched_user, :class_name => "User", :foreign_key => "watched_user_id"
  
  def after_initialize
    self.planwatch_group_id = 1 unless planwatch_group_id
  end
end
