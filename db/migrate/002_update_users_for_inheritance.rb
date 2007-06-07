# Remove the "remote" flag in favor of inheritance
class UpdateUsersForInheritance < ActiveRecord::Migration
  
  # Define a skeleton User hierarchy in this class' scope to avoid version collisions
  class User < ActiveRecord::Base; end
  class LocalUser < User; end
  class RemoteUser < User; end
  
  def self.up
    # "type" is the columns AR uses as a discriminator by default
    add_column :users, :type, :string
    
    User.find(:all).each do |user|
      user.type = user.remote? ? "RemoteUser" : "LocalUser"
      user.save!
    end
    
    remove_column :users, :remote
  end

  def self.down
    add_column :users, :remote, :boolean
    
    User.find(:all).each do |user|
      user.remote = user.is_a?(RemoteUser) ? true : false
      user.save!
    end
    
    remove_column :users, :type
  end
end
