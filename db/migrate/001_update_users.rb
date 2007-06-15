# Convert the legacy schema for users to a Rails-friendly version
class UpdateUsers < ActiveRecord::Migration

  # Define a skeleton User in this class' scope to avoid version collisions
  class User < ActiveRecord::Base; end
  
  def self.up
    add_column :users, :new_remote,  :boolean
    add_column :users, :new_world,   :boolean
    add_column :users, :new_snitch,  :boolean
    add_column :users, :new_archive, :string
    add_column :users, :new_snitch_activated, :datetime
    add_column :users, :new_last_login,  :datetime
    add_column :users, :last_updated_at, :datetime
    add_column :users, :created_at, :datetime
    add_column :users, :updated_at, :datetime

    
    User.find(:all).each do |user|
      user.new_remote  = user.remote == 'Y' ? true : false
      user.new_world   = user.world == 'Y' ? true : false
      user.new_snitch  = user.snitch == 'Y' ? true : false
      user.new_archive = user.archive
      
      user.new_snitch_activated = Time.at(user.snitch_activated)
      user.new_last_login       = Time.at(user.last_login)
      user.last_updated_at      = Time.at(user.last_update)
      user.created_at           = Time.at(user.first_login) if user.first_login
      user.save!
    end
    
    remove_column :users, :remote
    remove_column :users, :world
    remove_column :users, :snitch
    remove_column :users, :archive
    remove_column :users, :snitch_activated
    remove_column :users, :last_login
    remove_column :users, :last_update
    remove_column :users, :first_login
    
    rename_column :users, :new_remote,  :remote
    rename_column :users, :new_world,   :world_viewable
    rename_column :users, :new_snitch,  :snitch_enabled
    rename_column :users, :new_archive, :default_archive_settings
    rename_column :users, :new_snitch_activated, :snitch_activated_at
    rename_column :users, :new_last_login,  :last_logged_in_at
    
    execute "update users set snitch_activated_at = NULL where snitch_activated_at = FROM_UNIXTIME(0)"
    execute "update users set last_logged_in_at = NULL where last_logged_in_at = FROM_UNIXTIME(0)"
    execute "update users set last_updated_at = NULL where last_updated_at = FROM_UNIXTIME(0)"
    execute "update users set created_at = NULL where created_at = FROM_UNIXTIME(0)"
  end

  def self.down
  end
end
