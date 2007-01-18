#include <Turpitude.h>

//####################### global variables ##################################3
zend_object_handlers turpitude_jobject_handlers;
zend_class_entry* turpitude_jobject_class_entry;
zend_object_value turpitude_jobject_object_value;


//####################### method handlers ##################################3

void turpitude_jobject_method_javainvoke(turpitude_javaobject_object* jobj, int xargc, zval*** xargv, zval* return_value) {
    // check param count
    if (xargc < 1) 
        php_error(E_ERROR, "invalid number of arguments to method javaInvoke.");

    // check constructor validity
    if (Z_TYPE_P(*xargv[0]) != IS_OBJECT)
        php_error(E_ERROR, "invalid type for param 1 (method) in method javaInvoke, should be IS_OBJECT.");

    zval* methodval = *xargv[0];
    zend_object_value obj = methodval->value.obj;
    zend_class_entry* methodce = Z_OBJCE_P(methodval);

    if (strcmp(methodce->name, "TurpitudeJavaMethod") != 0)
        php_error(E_ERROR, "invalid type for param 1 (method) in method create, should be TurpitudeJavaMethod.");

    // get jmethod
    turpitude_javamethod_object* method = (turpitude_javamethod_object*)zend_object_store_get_object(*xargv[0] TSRMLS_CC);

    // call java method
    // there might be a better way to do this, but it's far too late
    // it's an awful lot of ifs, isn't it?
    jvalue retval;
    if (xargc > 1) {
        // we need to build the jvalue array
        jvalue args[xargc-1];
        for (int i=0; i < xargc-1; i++) {
            args[i] = zval_to_jvalue(turpitude_jenv, *xargv[i+1]);
        }
        switch (method->return_type) {
            case JAVA_VOID: 
                turpitude_jenv->CallVoidMethodA(jobj->java_object, method->java_method, args); 
                break;
            case JAVA_OBJECT:
                retval.l = turpitude_jenv->CallObjectMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_BOOLEAN:
                retval.z = turpitude_jenv->CallBooleanMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_BYTE:
                retval.b = turpitude_jenv->CallByteMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_CHAR:
                retval.c = turpitude_jenv->CallCharMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_SHORT:
                retval.s = turpitude_jenv->CallShortMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_INT:
                retval.i = turpitude_jenv->CallIntMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_LONG:
                retval.j = turpitude_jenv->CallLongMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_FLOAT:
                retval.f = turpitude_jenv->CallFloatMethodA(jobj->java_object, method->java_method, args);
                break;
            case JAVA_DOUBLE:
                retval.d = turpitude_jenv->CallDoubleMethodA(jobj->java_object, method->java_method, args);
                break;
        };
    } else {
        // no parameters given - just call the method
        switch (method->return_type) {
            case JAVA_VOID: 
                turpitude_jenv->CallVoidMethod(jobj->java_object, method->java_method); 
                break;
            case JAVA_OBJECT:
                retval.l = turpitude_jenv->CallObjectMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_BOOLEAN:
                retval.z = turpitude_jenv->CallBooleanMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_BYTE:
                retval.b = turpitude_jenv->CallByteMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_CHAR:
                retval.c = turpitude_jenv->CallCharMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_SHORT:
                retval.s = turpitude_jenv->CallShortMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_INT:
                retval.i = turpitude_jenv->CallIntMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_LONG:
                retval.j = turpitude_jenv->CallLongMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_FLOAT:
                retval.f = turpitude_jenv->CallFloatMethod(jobj->java_object, method->java_method);
                break;
            case JAVA_DOUBLE:
                retval.d = turpitude_jenv->CallDoubleMethod(jobj->java_object, method->java_method);
                break;
        };
    }

    //TODO: retval to zval
    jvalue_to_zval(turpitude_jenv, retval, method->return_type, return_value);
}


//####################### helpers ##################################3



static
    ZEND_BEGIN_ARG_INFO(turpitude_jobject_arginfo_zero, 0)
    ZEND_END_ARG_INFO();

static
    ZEND_BEGIN_ARG_INFO(turpitude_jobject_arginfo_get, 0)
    ZEND_ARG_INFO(0, index)
    ZEND_END_ARG_INFO();


static ZEND_BEGIN_ARG_INFO(turpitude_jobject_arginfo_set, 0)
     ZEND_ARG_INFO(0, index)
     ZEND_ARG_INFO(0, newval)
     ZEND_END_ARG_INFO();

//####################### object handlers ##################################3

void turpitude_jobject_construct(INTERNAL_FUNCTION_PARAMETERS) {
}

void turpitude_jobject_call(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__call called:\n");
    
    zval ***xargv, ***argv;
    int i = 0, xargc, argc = ZEND_NUM_ARGS();
    HashPosition pos;
    zval **param;

    // method name
    argv = (zval ***) safe_emalloc(sizeof(zval **), argc, 0);
    if (zend_get_parameters_array_ex(argc, argv) == FAILURE) {
        php_error(E_ERROR, "Couldn't fetch arguments into array.");
    }
    char* method_name = Z_STRVAL_P(*argv[0]);

    // method parameters
    xargc = zend_hash_num_elements(Z_ARRVAL_PP(argv[1]));
    xargv = (zval***) safe_emalloc(sizeof(zval **), xargc, 0);
    // iterate on argument HashTable
    zend_hash_internal_pointer_reset_ex(Z_ARRVAL_PP(argv[1]), &pos);
    while (zend_hash_get_current_data_ex(Z_ARRVAL_PP(argv[1]), (void **) &param, &pos) == SUCCESS) {
        xargv[i++] = param; 
        zend_hash_move_forward_ex(Z_ARRVAL_PP(argv[1]), &pos);
    }

    // extract jobject from this pointer
    zval* myval = getThis();
    turpitude_javaobject_object* jobj = (turpitude_javaobject_object*)zend_object_store_get_object(myval TSRMLS_CC);

    bool method_valid = false;
 
    // java invoke method
    if (strcmp(Z_STRVAL_P(*argv[0]), "javaInvoke") == 0) {
        turpitude_jobject_method_javainvoke(jobj, xargc, xargv, return_value);
        method_valid = true;
    } else {
        // first parameter might be a method signature
        if (Z_TYPE_P(*argv[0]) == IS_STRING) {
            zval* methodval;
            // try to convert *xargv[0] into a TurpitudeJavaMethod, store it into *xargv[0]
            make_turpitude_jmethod_instance(jobj->java_class, method_name, Z_STRVAL_P(*xargv[0]), *xargv[0]);
            turpitude_jobject_method_javainvoke(jobj, xargc, xargv, return_value);
            method_valid = true;
        } 
    }

    // error handling
    char* errmsg = (char*)emalloc(100 + strlen(method_name));
    memset(errmsg, 0, 99 + strlen(method_name));
    if (!method_valid) { 
        sprintf(errmsg, "Call to invalid method %s() on object of class TurpitudeJavaObject.", method_name);
        php_error(E_ERROR, errmsg);
    }

    // housekeeping
    efree(errmsg);
    efree(argv);
    efree(xargv);
}

void turpitude_jobject_tostring(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__tostring called\n");
}

void turpitude_jobject_get(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__get called\n");
    //php_error(E_ERROR, "Tried to directly get a property on object of class TurpitudeEnvironment.");
}

void turpitude_jobject_set(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__set called\n");
    //php_error(E_ERROR, "Tried to directly set a property on object of class TurpitudeEnvironment.");
}

void turpitude_jobject_sleep(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__sleep called\n");
}

void turpitude_jobject_wakeup(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__wakeup called\n");
}

void turpitude_jobject_destruct(INTERNAL_FUNCTION_PARAMETERS) {
    //printf("__destruct called\n");
}

int turpitude_jobject_cast(zval *readobj, zval *writeobj, int type TSRMLS_DC) {
    printf("__cast called\n");
    return FAILURE;
}

zend_object_iterator* turpitude_jobject_get_iterator(zend_class_entry *ce, zval *object, int by_ref TSRMLS_DC) {
    printf("get_iterator called\n");
    return NULL;
}

void turpitude_jobject_free_object(void *object TSRMLS_DC) {
    turpitude_javaobject_object* intern = (turpitude_javaobject_object*)object;
    zend_hash_destroy(intern->std.properties);
    FREE_HASHTABLE(intern->std.properties);
    efree(object);
}

void turpitude_jobject_destroy_object(void* object, zend_object_handle handle TSRMLS_DC) {
    //printf("destroy object called\n");
}

zend_object_value turpitude_jobject_create_object(zend_class_entry *class_type TSRMLS_DC) {
    zend_object_value obj;
    turpitude_javaobject_object* intern;
    zval tmp;

    intern = (turpitude_javaobject_object*)emalloc(sizeof(turpitude_javaobject_object));
    memset(intern, 0, sizeof(turpitude_javaobject_object));
    intern->std.ce = class_type;

    ALLOC_HASHTABLE(intern->std.properties);
    zend_hash_init(intern->std.properties, 0, NULL, ZVAL_PTR_DTOR, 0);
    zend_hash_copy(intern->std.properties,
                   &class_type->default_properties,
                   (copy_ctor_func_t) zval_add_ref,
                   (void *) &tmp, sizeof(zval *));
    obj.handle = zend_objects_store_put(intern,  
                                        (zend_objects_store_dtor_t) turpitude_jobject_destroy_object,
                                        (zend_objects_free_object_storage_t)turpitude_jobject_free_object,
                                        NULL TSRMLS_CC);
    obj.handlers = &turpitude_jobject_handlers;

    return obj;
}

function_entry turpitude_jobject_class_functions[] = {
    ZEND_FENTRY(__construct, turpitude_jobject_construct, NULL, ZEND_ACC_PRIVATE) 
    ZEND_FENTRY(__call, turpitude_jobject_call, turpitude_jobject_arginfo_set, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__tostring, turpitude_jobject_tostring, turpitude_jobject_arginfo_zero, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__get, turpitude_jobject_get, turpitude_jobject_arginfo_get, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__set, turpitude_jobject_set, turpitude_jobject_arginfo_set, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__sleep, turpitude_jobject_sleep, turpitude_jobject_arginfo_zero, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__wakeup, turpitude_jobject_wakeup, turpitude_jobject_arginfo_zero, ZEND_ACC_PUBLIC)
    ZEND_FENTRY(__destruct, turpitude_jobject_destruct, turpitude_jobject_arginfo_zero, ZEND_ACC_PUBLIC)
    {NULL, NULL, NULL}
};

//####################### API ##################################3

/**
 * creates the TurpitudeJavaObject class and injects it into the interpreter
 */
void make_turpitude_jobject() {
    // create class entry
    zend_class_entry* parent;
    zend_class_entry ce;

    zend_internal_function call, get, set;
    make_lambda(&call, turpitude_jobject_call);
    make_lambda(&get, turpitude_jobject_get);
    make_lambda(&set, turpitude_jobject_set);

    INIT_OVERLOADED_CLASS_ENTRY(ce, 
                                "TurpitudeJavaObject", 
                                turpitude_jobject_class_functions, 
                                (zend_function*)&call, 
                                (zend_function*)&get, 
                                (zend_function*)&set);

    memcpy(&turpitude_jobject_handlers, zend_get_std_object_handlers(), sizeof(turpitude_jobject_handlers));
    turpitude_jobject_handlers.cast_object = turpitude_jobject_cast;
    
    turpitude_jobject_class_entry = zend_register_internal_class(&ce TSRMLS_CC);
    turpitude_jobject_class_entry->get_iterator = turpitude_jobject_get_iterator;
    turpitude_jobject_class_entry->create_object = turpitude_jobject_create_object;
}

void make_turpitude_jobject_instance(jclass cls, zval* turpcls, jobject obj, zval* dest) {
    if (!dest)
        ALLOC_ZVAL(dest);

    // instantiate JavaObject object
    Z_TYPE_P(dest) = IS_OBJECT;
    object_init_ex(dest, turpitude_jobject_class_entry);
    dest->refcount = 1;
    dest->is_ref = 1;

    // assign jclass and jobject to object
    turpitude_javaobject_object* intern = (turpitude_javaobject_object*)zend_object_store_get_object(dest TSRMLS_CC);
    intern->java_class = cls;
    intern->java_object = obj;

    // add class reference as a property
    zend_hash_update(Z_OBJPROP_P(dest), "Class", sizeof("Class"), (void **) &turpcls, sizeof(zval *), NULL);
}

