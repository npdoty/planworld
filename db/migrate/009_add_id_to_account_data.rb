class AddIdToAccountData < ActiveRecord::Migration
  def self.up
    create_table :ids do |t|
      t.string :username
    end
    
    execute "INSERT INTO ids SELECT 0, Username FROM account_data"
    execute "ALTER TABLE account_data DROP PRIMARY KEY"
    add_column :account_data, :id, :integer
    execute "UPDATE account_data, ids SET account_data.id=ids.id WHERE ids.username=account_data.Username"
    execute "ALTER TABLE account_data ADD PRIMARY KEY(id)"
    
    drop_table :ids
  end

  def self.down
    execute "ALTER TABLE account_data DROP PRIMARY KEY"
    remove_column :account_data, :id
    execute "ALTER TABLE account_data ADD PRIMARY KEY(Username)"
  end
end
