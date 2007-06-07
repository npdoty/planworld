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

class LocalUser < User
end
