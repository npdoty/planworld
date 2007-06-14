require 'rubygems'
require 'test/spec'

require 'active_support'
require 'active_record'
require 'action_controller'

require File.dirname(__FILE__) + '/../lib/rails'
require File.dirname(__FILE__) + '/../lib/candy'

begin
  require 'colored'
rescue LoadError
  nil
end
