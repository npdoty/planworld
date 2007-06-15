# == Schema Information
#
# Table name: planwatch_groups
#
#  id         :integer(11)   not null, primary key
#  user_id    :integer(11)   
#  name       :string(255)   
#  position   :integer(11)   
#  updated_at :datetime      
#  created_at :datetime      
#

# A means of grouping watched Users within a Planwatch
class PlanwatchGroup < ActiveRecord::Base
  acts_as_list
  
  belongs_to :user
  # create 2 associations with differerent sorting
  has_many :planwatch_entries_sorted_by_last_update,
           :class_name => "PlanwatchEntry",
           # NOTE: this means that you can only ever see your own planwatch
           :conditions => 'user_id = #{User.current_user.id}', # single-quoted to be interpolated at runtime
           :order => "users.last_updated_at DESC",
           :include => :watched_user
  has_many :planwatch_entries_sorted_by_username,
           :class_name => "PlanwatchEntry",
           # NOTE: this means that you can only ever see your own planwatch
           :conditions => 'user_id = #{User.current_user.id}', # single-quoted to be interpolated at runtime
           :order => "users.username ASC",
           :include => :watched_user
  has_many :watched_users, :through => :planwatch_entries
  
  validates_presence_of :name
  
  # call the appropriate planwatch entry association based on the current user's preferences
  def planwatch_entries
    if User.current_user && User.current_user.watch_order == "alph"
      planwatch_entries_sorted_by_username
    else
      planwatch_entries_sorted_by_last_update
    end
  end
end
