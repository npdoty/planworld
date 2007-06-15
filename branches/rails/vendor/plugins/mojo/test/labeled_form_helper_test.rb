$:.unshift(File.dirname(__FILE__) + '/../lib')

require File.dirname(__FILE__) + '/../../../../config/environment'
require 'test/unit'
require 'rubygems'
require 'breakpoint'

require 'action_controller/test_process'

ActionController::Base.logger = nil
ActionController::Base.ignore_missing_templates = false
ActionController::Routing::Routes.reload rescue nil

silence_warnings do
  Post = Struct.new("Post", :title, :author_name, :body, :secret, :written_on, :cost)
  Post.class_eval do
    alias_method :title_before_type_cast, :title unless respond_to?(:title_before_type_cast)
    alias_method :body_before_type_cast, :body unless respond_to?(:body_before_type_cast)
    alias_method :author_name_before_type_cast, :author_name unless respond_to?(:author_name_before_type_cast)
    
    def self.required_attributes
      [:title]
    end
    
    def self.requires?(attr)
      required_attributes.include?(attr.to_sym)
    end
  end
end
  
class LabeledFormHelperTest < Test::Unit::TestCase
  include ActionView::Helpers::UrlHelper
  include ActionView::Helpers::TagHelper
  include ActionView::Helpers::TextHelper
  include ActionView::Helpers::FormHelper
  include ActionView::Helpers::FormTagHelper
  include MojoDNA::LabeledFormHelper

  def setup
    @post = Post.new
    def @post.errors() Class.new{ def on(field) field == "author_name" end }.new end

    def @post.id; 123; end
    def @post.id_before_type_cast; 123; end

    @post.title       = "Hello World"
    @post.author_name = ""
    @post.body        = "Back to the hill and over it again!"
    @post.secret      = 1
    @post.written_on  = Date.new(2004, 6, 15)

    @controller = Class.new do
      def url_for(options, *parameters_for_method_reference)
        "http://www.example.com"
      end
    end
    @controller = @controller.new
  end

  def test_label
    assert_dom_equal '<label for="post_body">Body</label>', label_for('post', 'body')
    assert_dom_equal '<label for="post_body" class="foo">Body</label>', label_for('post', 'body', {:class => :foo})
    assert_dom_equal '<label for="post_body" class="foo">Text</label>', label_for('post', 'body', {:class => :foo, :text => 'Text'})
  end

  def test_labeled_form_for
    _erbout = ''

    labeled_form_for(:post, @post) do |f|
      _erbout.concat f.text_field(:title)
      _erbout.concat f.text_area(:body)
      _erbout.concat f.check_box(:secret)
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='post_secret'>Secret</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>" +
      "</form>"

    assert_dom_equal expected, _erbout
  end
  
  def test_labeled_form_for_with_disabled_labeling
    _erbout = ''

    labeled_form_for(:post, @post) do |f|
      _erbout.concat f.text_field(:title, :label => false)
      _erbout.concat f.text_area(:body, :label => false)
      _erbout.concat f.check_box(:secret, :label => false)
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<input name='post[title]' size='30' type='text' id='post_title' value='Hello World' />" +
      "<textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea>" +
      "<input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' />" +
      "</form>"

    assert_dom_equal expected, _erbout
  end
  
  def test_labeled_form_for_with_custom_labels
    _erbout = ''

    labeled_form_for(:post, @post) do |f|
      _erbout.concat f.text_field(:title, :label => "Titre")
      _erbout.concat f.text_area(:body, :label => "Description")
      _erbout.concat f.check_box(:secret, :label => "Hidden")
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<p><label class='block' for='post_title'>Titre</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Description</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='post_secret'>Hidden</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>" +
      "</form>"

    assert_dom_equal expected, _erbout
  end
  
  def test_labeled_form_for_with_forcible_required
    _erbout = ''

    labeled_form_for(:post, @post) do |f|
      _erbout.concat f.text_field(:title)
      _erbout.concat f.text_area(:body, :required => true)
      _erbout.concat f.check_box(:secret)
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_secret'>Secret</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>" +
      "</form>"

    assert_dom_equal expected, _erbout
  end

  def test_labeled_form_for_with_single_argument
    _erbout = ''

    labeled_form_for(:post) do |f|
      _erbout.concat f.text_field(:title)
      _erbout.concat f.text_area(:body)
      _erbout.concat f.check_box(:secret)
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span>" +
      "<span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='post_secret'>Secret</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>" +
      "</form>"

    assert_dom_equal expected, _erbout
  end

  def test_labeled_form_for_and_fields_for
    _erbout = ''

    labeled_form_for(:post, @post) do |post_form|
      _erbout.concat post_form.text_field(:title)
      _erbout.concat post_form.text_area(:body)

      post_form.fields_for(:parent_post, @post) do |parent_fields|
        _erbout.concat parent_fields.check_box(:secret)
      end
    end

    expected = 
      "<form action='http://www.example.com' method='post'>" +
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='parent_post_secret'>Secret</label>" +
      "<span class='fields'><input name='parent_post[secret]' checked='checked' type='checkbox' id='parent_post_secret' value='1' />" +
      "<input name='parent_post[secret]' type='hidden' value='0' /></span></p>" +
      "</form>"

    assert_dom_equal expected, _erbout
  end

  def test_labeled_fields_for
    _erbout = ''

    labeled_fields_for(:post, @post) do |f|
      _erbout.concat f.text_field(:title)
      _erbout.concat f.text_area(:body)
      _erbout.concat f.check_box(:secret)
    end

    expected = 
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span><span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='post_secret'>Secret</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>"

    assert_dom_equal expected, _erbout
  end

  def test_labeled_fields_for_with_single_attribute
    _erbout = ''

    labeled_fields_for(:post) do |f|
      _erbout.concat f.text_field(:title)
      _erbout.concat f.text_area(:body)
      _erbout.concat f.check_box(:secret)
    end

    expected = 
      "<p><label class='block' for='post_title'>Title</label>" +
      "<span class='fields'><input name='post[title]' size='30' type='text' id='post_title' value='Hello World' /></span>" +
      "<span class='required'>* required</span></p>" +
      "<p><label class='block' for='post_body'>Body</label>" +
      "<span class='fields'><textarea name='post[body]' id='post_body' rows='20' cols='40'>Back to the hill and over it again!</textarea></span></p>" +
      "<p><label class='block' for='post_secret'>Secret</label>" +
      "<span class='fields'><input name='post[secret]' checked='checked' type='checkbox' id='post_secret' value='1' />" +
      "<input name='post[secret]' type='hidden' value='0' /></span></p>"

    assert_dom_equal expected, _erbout
  end

  # form tag helpers
  def test_label_tag
    assert_dom_equal %(<label for="title">Page Title</label>), label_tag('title', 'Page Title')
  end

  def test_label_tag_with_class
    assert_dom_equal %(<label for="title" class="frm_txt">Page Title</label>), label_tag('title', 'Page Title', :class => 'frm_txt')
  end
end