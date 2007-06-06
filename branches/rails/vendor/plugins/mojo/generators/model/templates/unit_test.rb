require File.dirname(__FILE__) + '<%= '/..' * class_nesting_depth %>/../test_helper'

module <%= class_name %>SpecHelper
  def create_<%= class_name.underscore %>(options = {})
    <%= class_name %>.create(options.reverse_merge({}))
  end
end

context "A <%= class_name %> (in general)" do
  fixtures :<%= table_name %>
  
<% for attribute in attributes -%>
  specify "should have a <%= attribute.name %>" do
    <%= table_name %>(:first).<%= attribute.name %>.should.not.be.nil
  end

<% end -%>
end

context "A new <%= class_name %>" do
  include <%= class_name %>SpecHelper
  fixtures :<%= table_name %>
  
  specify "should be created" do
    create_<%= class_name.underscore %>.should.validate
  end
end
