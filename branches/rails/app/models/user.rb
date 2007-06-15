# == Schema Information
#
# Table name: users
#
#  id                       :integer(10)   not null, primary key
#  username                 :string(128)   default(""), not null
#  snitch_views             :integer(6)    default(25), not null
#  archive_size_pub         :integer(11)   default(0), not null
#  archive_size             :integer(11)   default(0), not null
#  views                    :integer(11)   default(0), not null
#  watch_order              :string(6)     default("alph"), not null
#  theme_id                 :integer(6)    default(1), not null
#  last_ip                  :string(15)    
#  world_viewable           :boolean(1)    
#  snitch_enabled           :boolean(1)    
#  default_archive_settings :string(255)   
#  snitch_activated_at      :datetime      
#  last_logged_in_at        :datetime      
#  created_at               :datetime      
#  updated_at               :datetime      
#  type                     :string(255)   
#

class User < ActiveRecord::Base
  extend PlanworldNetAuthentication
  
  has_one :plan
  has_many :planwatch, :class_name => "PlanwatchGroup", :finder_sql =>
        'SELECT planwatch_groups.* ' +
        'FROM planwatch_groups ' +
        'WHERE user_id = #{id} OR user_id=0 ' +
        'ORDER BY user_id, name ASC' do
    def count
      PlanwatchEntry.count(:conditions => ["planwatch_entries.user_id=?", User.current_user.id])
    end

    def unread_count
      PlanwatchEntry.count(:conditions => ["planwatch_entries.user_id=? AND users.last_updated_at > planwatch_entries.last_viewed_at", User.current_user.id], :joins => "LEFT JOIN users ON users.id=planwatch_entries.watched_user_id")
    end
  end
        
  has_many :planwatch2, :class_name => "PlanwatchGroup", :order => "name ASC", :foreign_key => "user_id" do
    def count
      PlanwatchEntry.count(:conditions => ["planwatch_entries.user_id=?", User.current_user.id])
    end
    
    def unread_count
      PlanwatchEntry.count(:conditions => ["planwatch_entries.user_id=? AND users.last_updated_at > planwatch_entries.last_viewed_at", User.current_user.id], :joins => "LEFT JOIN users ON users.id=planwatch_entries.watched_user_id")
    end
  end
  
  def admin?
    false
  end
  
  def to_param
    username
  end
end
