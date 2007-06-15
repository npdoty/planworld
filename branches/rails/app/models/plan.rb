# == Schema Information
#
# Table name: plans
#
#  id         :integer(10)   not null, primary key
#  user_id    :integer(20)   default(0), not null
#  body       :text          
#  created_at :datetime      
#  updated_at :datetime      
#

class Plan < ActiveRecord::Base
  belongs_to :user
end
