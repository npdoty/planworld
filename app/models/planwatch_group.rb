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
  has_many :planwatch_entries
  has_many :watched_users, :through => :planwatch_entries
  
  validates_presence_of :name
end
