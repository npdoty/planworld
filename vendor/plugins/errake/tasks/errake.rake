task :remigrate => "db:remigrate" do
end

namespace :db do
  desc "Migrate down, then up, and load fixtures"
  task :remigrate => :environment do
    raise "Can't remigrate in this RAILS_ENV!" unless %w(development test build staging).include? RAILS_ENV

    no_fkey_checks do
      ActiveRecord::Base.connection.tables.each { |t| ActiveRecord::Base.connection.drop_table t }
    end

    Rake::Task["db:migrate"].invoke
    Rake::Task["db:fixtures:load"].invoke if ENV['LOAD_FIXTURES']
    Rake::Task["db:after_remigrate"].invoke rescue nil
  end

  def no_fkey_checks
    adapter_name = ActiveRecord::Base.connection.class.name.split('::').last.underscore.sub('_adapter','')

    fk_toggle(adapter_name, :on)
    yield
    fk_toggle(adapter_name, :off)
  end

  def fk_toggle(adapter_name, status)
    map = Hash.new({})
    map[:mysql] = { :on => "SET FOREIGN_KEY_CHECKS = 0", :off => "SET FOREIGN_KEY_CHECKS = 1" }

    if query = map[adapter_name.to_sym][status]
      ActiveRecord::Base.connection.execute query 
    end
  end

  desc "Returns the current schema version"
  task :version => :environment do
    puts "Current version: " + ActiveRecord::Migrator.current_version.to_s
  end
end

task :setup_sh_mysql do
  def sh_mysql(command = 'mysql', env = RAILS_ENV)
    require 'yaml'
    config = YAML.load(open(File.join('config', 'database.yml')).read.gsub(/^<%.+$/,''))[env]
    mysql = ''
    mysql << command << ' '
    mysql << "-u#{config['username']} " if config['username']
    mysql << "-p#{config['password']} " if config['password']
    mysql << "-h#{config['host']} "     if config['host']
    mysql << "-P#{config['port']} "     if config['port']
    mysql << "#{config['database']}"    if config['database']
  end
end

desc "Launch mysql shell.  Use with an environment task (e.g. rake production mysql)"
task :mysql => :setup_sh_mysql do
  system sh_mysql
end

%w(development production staging).each do |env|
  desc "Runs the following task in the #{env} environment" 
  task env do
    RAILS_ENV = ENV['RAILS_ENV'] = env
  end
end

desc "Runs the following task in the test environment" 
task :testing do
  RAILS_ENV = ENV['RAILS_ENV'] = 'test'
end

task :dev do
  Rake::Task["development"].invoke
end

task :prod do
  Rake::Task["production"].invoke
end

desc "Print out all the currently defined routes, with names."
task :routes => :environment do
  name_col_width = ActionController::Routing::Routes.named_routes.routes.keys.sort {|a,b| a.to_s.size <=> b.to_s.size}.last.to_s.size
  ActionController::Routing::Routes.routes.each do |route|
    name = ActionController::Routing::Routes.named_routes.routes.index(route).to_s
    name = name.ljust(name_col_width + 1)
    puts "#{name}#{route}"
  end
end

desc "Print plugins managed by Piston."           
task :pistoned do
  Dir['vendor/plugins/*'].each do |file|                                                                                               
    unless (revision = `svn propget piston:remote-revision #{file}`.strip).empty?                                                      
      puts "#{file.split('/').last}: r#{revision} [#{`svn propget piston:root #{file}`.strip}]"                                        
    end                                                                                                                                
  end                                                                                                                                  
end 

desc "Converts a YAML file into a test/spec skeleton"
task :yaml_to_spec do
  require 'yaml'

  puts YAML.load_file(ENV['FILE']||!puts("Pass in FILE argument.")&&exit).inject(''){|t,(c,s)|
    t+(s ?%.context "#{c}" do.+s.map{|d|%.\n  xspecify "#{d}" do\n  end\n.}*''+"end\n\n":'')
  }.strip
end

desc "Show specs when testing"
task :spec do
  ENV['TESTOPTS'] = '--runner=s'
  Rake::Task[:test].invoke
end

%w(functionals units integration).each do |type|
  namespace :spec do
    desc "Show specs when testing #{type}"
    task type do
      ENV['TESTOPTS'] = '--runner=s'
      Rake::Task["test:#{type}"].invoke
    end
  end
end
